# picture-checker-api/app.py
from flask import Flask, request, jsonify, Response
from flask_cors import CORS
import cv2
import numpy as np
import base64
import os
import tempfile
from datetime import datetime
import sys
import json
import time
from rembg import remove
from PIL import Image
import io

# Add scripts directory to Python path
scripts_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'scripts')
sys.path.insert(0, scripts_path)

from checker import PictureChecker
from config import *

app = Flask(__name__)
CORS(app)

# Initialize the checker
checker = PictureChecker()

# Create directories
os.makedirs("uploads", exist_ok=True)
os.makedirs("approved", exist_ok=True)

def base64_to_image(base64_string):
    """Convert base64 string to OpenCV image"""
    try:
        if ',' in base64_string:
            base64_string = base64_string.split(',')[1]
        
        img_data = base64.b64decode(base64_string)
        nparr = np.frombuffer(img_data, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        return img
    except Exception as e:
        print(f"Error decoding base64: {e}")
        return None

def image_to_base64_png(image):
    """Convert OpenCV image to base64 PNG string"""
    try:
        # Convert to PNG
        _, buffer = cv2.imencode('.png', image)
        img_str = base64.b64encode(buffer).decode('utf-8')
        return f"data:image/png;base64,{img_str}"
    except Exception as e:
        print(f"Error encoding to PNG: {e}")
        # Fallback to JPEG
        return image_to_base64_jpeg(image)

def image_to_base64_jpeg(image):
    """Convert OpenCV image to base64 JPEG string (fallback)"""
    try:
        _, buffer = cv2.imencode('.jpg', image, [cv2.IMWRITE_JPEG_QUALITY, 90])
        img_str = base64.b64encode(buffer).decode('utf-8')
        return f"data:image/jpeg;base64,{img_str}"
    except Exception as e:
        print(f"Error encoding to JPEG: {e}")
        return None

      
def apply_white_background_with_rembg(image):
    """
    High-quality white background with halo removal
    """
    try:
        # OpenCV → PIL
        image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        pil_image = Image.fromarray(image_rgb)

        # Remove background
        output = remove(pil_image)  # RGBA
        output_np = np.array(output)

        if output_np.shape[2] != 4:
            return cv2.cvtColor(output_np, cv2.COLOR_RGB2BGRA)

        rgb = output_np[:, :, :3].astype(np.float32)
        alpha = output_np[:, :, 3].astype(np.float32)

        # ---------- 🔧 ALPHA CLEANUP ----------
        # Remove weak transparency (halo killer)
        alpha[alpha < 40] = 0
        alpha[alpha > 240] = 255

        # Slight blur for smooth edge
        alpha = cv2.GaussianBlur(alpha, (5, 5), 0)

        # Normalize
        alpha = alpha / 255.0
        alpha = alpha[:, :, None]

        # White background
        white_bg = np.ones_like(rgb, dtype=np.float32) * 255

        # Proper alpha blending
        blended = rgb * alpha + white_bg * (1 - alpha)

        blended = blended.astype(np.uint8)

        # Convert to BGRA
        result = cv2.cvtColor(blended, cv2.COLOR_RGB2BGRA)
        result[:, :, 3] = 255

        return result

    except Exception as e:
        print("rembg failed:", e)
        return cv2.cvtColor(image, cv2.COLOR_BGR2BGRA)
    

# def apply_white_background_fallback(image):
#     """Fallback method for white background (simpler)"""
#     try:
#         # Add white border
#         image_with_border = cv2.copyMakeBorder(
#             image, 20, 20, 20, 20, 
#             cv2.BORDER_CONSTANT, 
#             value=[255, 255, 255]  # White border
#         )
        
#         # Convert to BGRA
#         bgra = cv2.cvtColor(image_with_border, cv2.COLOR_BGR2BGRA)
        
#         # Create gradient white background effect
#         h, w = bgra.shape[:2]
#         gradient = np.linspace(240, 255, h).astype(np.uint8)
#         gradient = np.tile(gradient[:, np.newaxis, np.newaxis], (1, w, 4))
        
#         # Simple face detection for mask
#         gray = cv2.cvtColor(image_with_border, cv2.COLOR_BGR2GRAY)
#         face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
#         faces = face_cascade.detectMultiScale(gray, 1.1, 4)
        
#         mask = np.zeros((h, w), dtype=np.uint8)
#         if len(faces) > 0:
#             for (x, y, w_f, h_f) in faces:
#                 # Adjust coordinates for border
#                 x += 20
#                 y += 20
#                 cv2.ellipse(mask, (x + w_f//2, y + h_f//2), (w_f//2, int(h_f*0.7)), 0, 0, 360, 255, -1)
            
#             mask = cv2.GaussianBlur(mask, (21, 21), 0)
#             mask_normalized = mask / 255.0
            
#             # Composite
#             for c in range(3):
#                 gradient[:, :, c] = bgra[:, :, c] * mask_normalized[:, :, np.newaxis] + \
#                                    gradient[:, :, c] * (1 - mask_normalized[:, :, np.newaxis])
            
#             gradient[:, :, 3] = 255
        
#         return gradient.astype(np.uint8)
        
#     except Exception as e:
#         print(f"Error in fallback background: {e}")
#         # Last resort: return original image
#         return cv2.cvtColor(image, cv2.COLOR_BGR2BGRA)



# def create_professional_picture(image):
    """Create professional picture with white background"""
    # Apply white background using rembg
    professional_image = apply_white_background_with_rembg(image)
    
    # Resize to standard passport size (600x600)
    target_size = (600, 600)
    
    # Calculate resize maintaining aspect ratio
    h, w = professional_image.shape[:2]
    scale = min(target_size[0]/w, target_size[1]/h)
    new_w, new_h = int(w * scale), int(h * scale)
    
    # Resize with high quality
    resized = cv2.resize(professional_image, (new_w, new_h), interpolation=cv2.INTER_LANCZOS4)
    
    # Create canvas with white background
    canvas = np.ones((target_size[1], target_size[0], 4), dtype=np.uint8) * 255
    
    # Center the image on canvas
    x_offset = (target_size[0] - new_w) // 2
    y_offset = (target_size[1] - new_h) // 2
    
    # Place image on canvas (with alpha blending)
    if resized.shape[2] == 4:
        alpha = resized[:, :, 3] / 255.0
        
        for c in range(3):
            canvas[y_offset:y_offset+new_h, x_offset:x_offset+new_w, c] = \
                resized[:, :, c] * alpha + \
                canvas[y_offset:y_offset+new_h, x_offset:x_offset+new_w, c] * (1 - alpha)
    else:
        canvas[y_offset:y_offset+new_h, x_offset:x_offset+new_w, :3] = resized
    
    canvas[:, :, 3] = 255  # Fully opaque
    
    return canvas
def create_professional_picture(image):
    """Create professional picture with blue background - NO TEXT"""
    try:
        # Apply blue background
        professional_image = apply_white_background_with_rembg(image)
        
        # Resize to standard passport size (600x600)
        target_size = (600, 600)
        
        # Calculate resize maintaining aspect ratio
        h, w = professional_image.shape[:2]
        scale = min(target_size[0]/w, target_size[1]/h)
        new_w, new_h = int(w * scale), int(h * scale)
        
        # Resize
        resized = cv2.resize(professional_image, (new_w, new_h), interpolation=cv2.INTER_LANCZOS4)
        
        # Create canvas with blue background
        canvas = np.ones((target_size[1], target_size[0], 4), dtype=np.uint8) * [255, 0, 0, 255]  # Blue
        
        # Center the image on canvas
        x_offset = (target_size[0] - new_w) // 2
        y_offset = (target_size[1] - new_h) // 2
        
        # Place image on canvas
        canvas[y_offset:y_offset+new_h, x_offset:x_offset+new_w] = resized
        
        # Ensure NO text is added
        # We're not calling any drawing functions here
        
        return canvas
        
    except Exception as e:
        print(f"Error creating professional picture: {e}")
        # Return original image as fallback
        return cv2.cvtColor(image, cv2.COLOR_BGR2BGRA)
    

def get_feedback_messages(issues, is_valid):
    """Convert issues list to feedback messages"""
    feedback = []
    
    if not is_valid:
        for issue in issues:
            if "No face" in issue:
                feedback.append("❌ No face detected - Position yourself in center")
            elif "Multiple faces" in issue:
                feedback.append("⚠️ Multiple faces detected - Ensure only one person is in frame")
            elif "tilted" in issue.lower():
                feedback.append("⚠️ Head is tilted - Keep your head straight")
            elif "turn" in issue.lower() or "yaw" in issue.lower():
                feedback.append("⚠️ Turn your face forward - Look straight at camera")
            elif "look straight" in issue.lower():
                feedback.append("⚠️ Look straight ahead - Don't look up or down")
            elif "mouth" in issue.lower():
                feedback.append("⚠️ Close your mouth for professional photo")
            elif "too small" in issue.lower():
                feedback.append("⚠️ Move closer to camera - Face is too small")
            elif "too large" in issue.lower():
                feedback.append("⚠️ Move away from camera - Face is too large")
            else:
                feedback.append(f"⚠️ {issue}")
    else:
        feedback = [
            "✅ Single face detected - Good!",
            "✅ Head straight - Perfect!",
            "✅ Looking straight - Good eye contact!",
            "✅ Mouth closed - Professional!",
            "✅ Good lighting and clear image"
        ]
    
    return feedback

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'ok',
        'service': 'picture-validator',
        'rembg_available': True,
        'timestamp': datetime.now().isoformat()
    })

# @app.route('/api/validate-image', methods=['POST'])
# def validate_image():
    """Validate an uploaded image"""
    try:
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image data provided'
            }), 400
        
        # Get image data
        image_data = data['image']
        
        # Convert base64 to image
        image = base64_to_image(image_data)
        if image is None:
            return jsonify({
                'success': False,
                'error': 'Invalid image data'
            }), 400
        
        # Get mode (webcam or upload)
        mode = data.get('mode', 'upload')
        draw_feedback = data.get('draw_feedback', True)
        professional_format = data.get('professional_format', True)
        
        # Validate image using your existing checker
        is_valid, issues, annotated_image = checker.validate_image(image, draw_feedback=draw_feedback)
        
        # Save to uploads folder
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        upload_filename = f"uploads/{timestamp}_{mode}.jpg"
        cv2.imwrite(upload_filename, image)
        
        # If valid, save to approved folder
        if is_valid:
            approved_filename = f"approved/{timestamp}_approved.jpg"
            cv2.imwrite(approved_filename, image)
            
            # Also save professional version with white background
            if professional_format:
                professional_image = create_professional_picture(image)
                professional_filename = f"approved/{timestamp}_professional.png"
                cv2.imwrite(professional_filename, professional_image)
        
        # Convert annotated image to base64
        if professional_format and is_valid:
            # Create professional version with white background
            professional_image = create_professional_picture(image)
            annotated_base64 = image_to_base64_png(professional_image)
            image_format = 'png'
            has_white_background = True
        else:
            # Use regular annotated image
            annotated_base64 = image_to_base64_jpeg(annotated_image)
            image_format = 'jpeg'
            has_white_background = False
        
        # Generate feedback messages
        feedback = get_feedback_messages(issues, is_valid)
        
        return jsonify({
            'success': True,
            'valid': is_valid,
            'issues': issues,
            'feedback': feedback,
            'annotated_image': annotated_base64,
            'image_format': image_format,
            'has_white_background': has_white_background,
            'background_color': 'white' if has_white_background else 'original',
            'upload_path': upload_filename,
            'approved_path': f"approved/{timestamp}_approved.jpg" if is_valid else None,
            'professional_path': f"approved/{timestamp}_professional.png" if (is_valid and professional_format) else None,
            'timestamp': timestamp,
            'mode': mode
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500
@app.route('/api/validate-image', methods=['POST'])
def validate_image():
    """Validate an uploaded image"""
    try:
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image data provided'
            }), 400
        
        # Get image data
        image_data = data['image']
        
        # Convert base64 to image
        image = base64_to_image(image_data)
        if image is None:
            return jsonify({
                'success': False,
                'error': 'Invalid image data'
            }), 400
        
        # Get mode (webcam or upload)
        mode = data.get('mode', 'upload')
        draw_feedback = data.get('draw_feedback', True)
        professional_format = data.get('professional_format', True)  # New parameter
        
        # Validate image using your existing checker
        # For professional format, we don't want text on the image
        if professional_format:
            # First validate without drawing feedback
            is_valid, issues, _ = checker.validate_image(image, draw_feedback=False)
            
            # If valid, create clean image for professional format
            if is_valid:
                # Create professional version WITHOUT any text
                professional_image = create_professional_picture(image)
                annotated_base64 = image_to_base64_png(professional_image)
                
                # But we still need an annotated version for display
                _, _, annotated_for_display = checker.validate_image(image, draw_feedback=True)
                display_base64 = image_to_base64_jpeg(annotated_for_display)
            else:
                # If not valid, show annotated image with feedback
                _, _, annotated_for_display = checker.validate_image(image, draw_feedback=True)
                annotated_base64 = image_to_base64_jpeg(annotated_for_display)
                display_base64 = annotated_base64
        else:
            # Regular validation with feedback
            is_valid, issues, annotated_image = checker.validate_image(image, draw_feedback=draw_feedback)
            annotated_base64 = image_to_base64_jpeg(annotated_image)
            display_base64 = annotated_base64
        
        # Save to uploads folder
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        upload_filename = f"uploads/{timestamp}_{mode}.jpg"
        cv2.imwrite(upload_filename, image)
        
        # If valid, save to approved folder
        if is_valid:
            approved_filename = f"approved/{timestamp}_approved.jpg"
            cv2.imwrite(approved_filename, image)
            
            # Also save professional version with blue background (without text)
            if professional_format and is_valid:
                professional_image = create_professional_picture(image)
                professional_filename = f"approved/{timestamp}_professional.png"
                cv2.imwrite(professional_filename, professional_image)
        
        # Generate feedback messages
        feedback = get_feedback_messages(issues, is_valid)
        
        return jsonify({
            'success': True,
            'valid': is_valid,
            'issues': issues,
            'feedback': feedback,
            'annotated_image': annotated_base64,  # This is the clean professional image
            'display_image': display_base64,       # This is for display with feedback
            'image_format': 'png' if (professional_format and is_valid) else 'jpeg',
            'has_blue_background': professional_format and is_valid,
            'is_clean_image': professional_format and is_valid,  # No text on image
            'upload_path': upload_filename,
            'approved_path': f"approved/{timestamp}_approved.jpg" if is_valid else None,
            'professional_path': f"approved/{timestamp}_professional.png" if (is_valid and professional_format) else None,
            'timestamp': timestamp,
            'mode': mode
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/validate-webcam-frame', methods=['POST'])
def validate_webcam_frame():
    """Validate webcam frame with real-time feedback"""
    try:
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image data provided'
            }), 400
        
        # Convert base64 to image
        image = base64_to_image(data['image'])
        if image is None:
            return jsonify({
                'success': False,
                'error': 'Invalid image data'
            }), 400
        
        # Validate with real-time feedback (always draw feedback for webcam)
        is_valid, issues, annotated_image = checker.validate_image(image, draw_feedback=True)
        
        # Convert annotated image to base64 (JPEG for webcam preview)
        annotated_base64 = image_to_base64_jpeg(annotated_image)
        
        # Generate real-time feedback messages
        feedback = get_feedback_messages(issues, is_valid)
        
        # Generate specific real-time instructions
        realtime_feedback = []
        
        # Check each issue for specific guidance
        for issue in issues:
            if "No face" in issue:
                realtime_feedback.append("❌ No face detected - Move into frame")
            elif "Multiple faces" in issue:
                realtime_feedback.append("⚠️ Multiple faces - Ensure only you are visible")
            elif "tilted" in issue.lower():
                if "left" in issue.lower():
                    realtime_feedback.append("⚠️ Head tilted left - Straighten to right")
                elif "right" in issue.lower():
                    realtime_feedback.append("⚠️ Head tilted right - Straighten to left")
                else:
                    realtime_feedback.append("⚠️ Head is tilted - Keep head straight")
            elif "yaw" in issue.lower() or "turn" in issue.lower():
                realtime_feedback.append("⚠️ Face not straight - Look directly at camera")
            elif "mouth" in issue.lower():
                realtime_feedback.append("⚠️ Mouth is open - Close your mouth")
            elif "too small" in issue.lower():
                realtime_feedback.append("⚠️ Move closer - Face is too small")
            elif "too large" in issue.lower():
                realtime_feedback.append("⚠️ Move back - Face is too large")
        
        # If no issues but not perfect yet
        if not issues and not is_valid:
            realtime_feedback.append("✅ Good start! Keep adjusting...")
        
        # If valid, show success messages
        if is_valid:
            realtime_feedback = [
                "✅ Perfect! Single face detected",
                "✅ Head is straight",
                "✅ Looking directly at camera",
                "✅ Mouth is closed",
                "✅ Good lighting and position"
            ]
        
        # If we have no realtime feedback yet but have issues
        if not realtime_feedback and issues:
            realtime_feedback = [f"⚠️ {issue}" for issue in issues]
        
        return jsonify({
            'success': True,
            'valid': is_valid,
            'issues': issues,
            'feedback': feedback,
            'realtime_feedback': realtime_feedback,
            'annotated_image': annotated_base64,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/create-professional-picture', methods=['POST'])
def create_professional_picture_api():
    """Create professional picture with white background"""
    try:
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image data provided'
            }), 400
        
        # Convert base64 to image
        image = base64_to_image(data['image'])
        if image is None:
            return jsonify({
                'success': False,
                'error': 'Invalid image data'
            }), 400
        
        # Create professional picture
        professional_image = create_professional_picture(image)
        
        # Save to approved folder
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"approved/professional_{timestamp}.png"
        cv2.imwrite(filename, professional_image)
        
        # Convert to base64 PNG
        professional_base64 = image_to_base64_png(professional_image)
        
        return jsonify({
            'success': True,
            'message': 'Professional picture created successfully',
            'professional_image': professional_base64,
            'filename': filename,
            'path': os.path.abspath(filename),
            'format': 'png',
            'background': 'white',
            'dimensions': f"{professional_image.shape[1]}x{professional_image.shape[0]}",
            'timestamp': timestamp
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/get-validation-status', methods=['GET'])
def get_validation_status():
    """Get current validation system status"""
    return jsonify({
        'success': True,
        'status': 'running',
        'model': 'PictureChecker',
        'rembg': 'available',
        'background': 'white',
        'requirements': [
            "Exactly one person in frame",
            "Face looking straight at camera",
            "Head not tilted left or right",
            "Mouth closed",
            "Good lighting and clear image"
        ]
    })

@app.route('/api/get-guidelines', methods=['GET'])
def get_guidelines():
    """Get picture validation guidelines"""
    return jsonify({
        'success': True,
        'guidelines': {
            'general': [
                "Stand in front of a plain background",
                "Use good, even lighting",
                "Wear professional attire",
                "Make sure only you are in the frame"
            ],
            'position': [
                "Look directly at the camera",
                "Keep your head straight (no tilt)",
                "Position your face in the center",
                "Keep shoulders level"
            ],
            'expression': [
                "Keep your mouth closed",
                "Maintain a neutral or slight smile",
                "Keep eyes open and natural",
                "Relax your facial muscles"
            ],
            'technical': [
                "Camera at eye level",
                "Good resolution and focus",
                "No red eye or glare",
                "Natural skin tones"
            ]
        }
    })

@app.route('/api/analyze-pose', methods=['POST'])
def analyze_pose():
    """Detailed pose analysis for debugging"""
    try:
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image data provided'
            }), 400
        
        image = base64_to_image(data['image'])
        if image is None:
            return jsonify({
                'success': False,
                'error': 'Invalid image data'
            }), 400
        
        # Your existing validation logic
        is_valid, issues, annotated_image = checker.validate_image(image, draw_feedback=True)
        
        # Get image dimensions
        img_height, img_width = image.shape[:2]
        
        # Convert to RGB for MediaPipe
        image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        
        # Detect faces for face count
        detection_results = checker.face_detection.process(image_rgb)
        num_faces = len(detection_results.detections) if detection_results.detections else 0
        
        # Get face mesh for detailed analysis
        mesh_results = checker.face_mesh.process(image_rgb)
        
        pose_analysis = {
            'num_faces': num_faces,
            'is_valid': is_valid,
            'issues': issues,
            'face_detected': num_faces > 0,
            'single_face': num_faces == 1
        }
        
        if mesh_results.multi_face_landmarks:
            landmarks = mesh_results.multi_face_landmarks[0].landmark
            
            # Import utilities
            from utils import get_head_pose, get_mouth_openness, get_face_size_ratio
            
            # Get detailed measurements
            pitch, yaw, roll = get_head_pose(landmarks, img_width, img_height)
            mouth_openness = get_mouth_openness(landmarks)
            
            pose_analysis.update({
                'head_pitch': float(pitch),
                'head_yaw': float(yaw),
                'head_roll': float(roll),
                'mouth_openness': float(mouth_openness),
                'head_tilt_detected': abs(roll) > MAX_TILT_ANGLE,
                'head_yaw_detected': abs(yaw) > MAX_YAW_ANGLE,
                'head_pitch_detected': abs(pitch) > MAX_PITCH_ANGLE,
                'mouth_open_detected': mouth_openness > MAX_MOUTH_OPEN
            })
        
        return jsonify({
            'success': True,
            'analysis': pose_analysis,
            'annotated_image': image_to_base64_jpeg(annotated_image) if annotated_image is not None else None
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    print("=" * 60)
    print("🚀 PROFESSIONAL PICTURE VALIDATOR API")
    print("=" * 60)
    print("📁 Uploads directory:", os.path.abspath("uploads"))
    print("✅ Approved directory:", os.path.abspath("approved"))
    print("🌐 API URL: http://localhost:5001")
    print("🔄 Using Rembg for background removal")
    print("⚪ White background professional pictures")
    print("📋 Health check: GET /api/health")
    print("📸 Webcam validation: POST /api/validate-webcam-frame")
    print("🖼️ Image validation: POST /api/validate-image")
    print("💼 Professional picture: POST /api/create-professional-picture")
    print("🔧 Pose analysis: POST /api/analyze-pose")
    print("=" * 60)
    print("✅ Ready to validate professional pictures!")
    print("=" * 60)
    
    app.run(debug=True, port=5001, host='0.0.0.0')