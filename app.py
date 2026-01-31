import streamlit as st
import numpy as np
from PIL import Image, ImageDraw
import io
import cv2
import requests
import os

# Page configuration - MUST be first Streamlit command
st.set_page_config(
    page_title="CV Photo Validator",
    page_icon="📸",
    layout="wide"
)

# Download face detection model if not exists
@st.cache_resource
def download_face_detector():
    """Download OpenCV DNN face detection model"""
    model_dir = "models"
    os.makedirs(model_dir, exist_ok=True)
    
    prototxt_path = os.path.join(model_dir, "deploy.prototxt")
    model_path = os.path.join(model_dir, "res10_300x300_ssd_iter_140000.caffemodel")
    
    # Download prototxt if not exists
    if not os.path.exists(prototxt_path):
        prototxt_url = "https://raw.githubusercontent.com/opencv/opencv/master/samples/dnn/face_detector/deploy.prototxt"
        response = requests.get(prototxt_url)
        with open(prototxt_path, 'wb') as f:
            f.write(response.content)
    
    # Download caffemodel if not exists
    if not os.path.exists(model_path):
        model_url = "https://github.com/opencv/opencv_3rdparty/raw/dnn_samples_face_detector_20170830/res10_300x300_ssd_iter_140000.caffemodel"
        response = requests.get(model_url)
        with open(model_path, 'wb') as f:
            f.write(response.content)
    
    # Load the model
    net = cv2.dnn.readNetFromCaffe(prototxt_path, model_path)
    return net

def detect_face_dnn(image, net):
    """Detect face using OpenCV DNN"""
    img_array = np.array(image)
    (h, w) = img_array.shape[:2]
    
    # Create blob from image
    blob = cv2.dnn.blobFromImage(cv2.resize(img_array, (300, 300)), 1.0,
        (300, 300), (104.0, 177.0, 123.0))
    
    # Pass the blob through the network
    net.setInput(blob)
    detections = net.forward()
    
    faces = []
    for i in range(0, detections.shape[2]):
        confidence = detections[0, 0, i, 2]
        if confidence > 0.5:
            box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
            (startX, startY, endX, endY) = box.astype("int")
            faces.append({
                'box': (startX, startY, endX, endY),
                'confidence': float(confidence)
            })
    
    return faces

def check_face_position(faces, img_width, img_height):
    """Check if face is centered and properly positioned"""
    if not faces:
        return False, False, False
    
    # Get the largest face
    largest_face = max(faces, key=lambda x: (x['box'][2] - x['box'][0]) * (x['box'][3] - x['box'][1]))
    (startX, startY, endX, endY) = largest_face['box']
    
    # Calculate face center
    face_center_x = (startX + endX) / 2
    face_center_y = (startY + endY) / 2
    
    img_center_x = img_width / 2
    img_center_y = img_height / 2
    
    # Check if face is centered (within 30% of center)
    is_centered_x = abs(face_center_x - img_center_x) < (img_width * 0.3)
    is_centered_y = abs(face_center_y - img_center_y) < (img_height * 0.3)
    
    # Check if face is too small or too large
    face_width = endX - startX
    face_height = endY - startY
    face_ratio = (face_width * face_height) / (img_width * img_height)
    
    is_good_size = 0.1 < face_ratio < 0.8
    
    return is_centered_x and is_centered_y, is_good_size, True

def check_eye_symmetry(img_array, faces):
    """Basic eye symmetry check using simple image processing"""
    if not faces:
        return True, 0.0
    
    # Get the largest face
    largest_face = max(faces, key=lambda x: (x['box'][2] - x['box'][0]) * (x['box'][3] - x['box'][1]))
    (startX, startY, endX, endY) = largest_face['box']
    
    # Extract face region
    face_roi = img_array[startY:endY, startX:endX]
    if face_roi.size == 0:
        return True, 0.0
    
    # Convert to grayscale
    if len(face_roi.shape) == 3:
        gray = cv2.cvtColor(face_roi, cv2.COLOR_RGB2GRAY)
    else:
        gray = face_roi
    
    # Simple symmetry check - compare left and right halves
    h, w = gray.shape
    if w < 10:
        return True, 0.0
    
    left_half = gray[:, :w//2]
    right_half = cv2.flip(gray[:, w//2:], 1)
    
    # Resize to same size if needed
    min_w = min(left_half.shape[1], right_half.shape[1])
    left_half = left_half[:, :min_w]
    right_half = right_half[:, :min_w]
    
    # Calculate symmetry score
    diff = cv2.absdiff(left_half, right_half)
    symmetry_score = 1.0 - (np.mean(diff) / 255.0)
    
    # Consider symmetric if score > 0.6
    is_symmetric = symmetry_score > 0.6
    
    return is_symmetric, symmetry_score

def validate_cv_photo(image):
    """Validate if photo meets CV requirements using OpenCV only"""
    issues = []
    details = {}
    
    # Load face detector
    try:
        net = download_face_detector()
    except Exception as e:
        issues.append(f"❌ Failed to load face detection model: {str(e)}")
        return False, issues, None, details
    
    # Convert PIL to numpy array
    img_array = np.array(image)
    img_rgb = img_array  # Already RGB from PIL
    
    # Detect faces
    faces = detect_face_dnn(image, net)
    
    if not faces:
        issues.append("❌ No face detected in the image")
        return False, issues, None, details
    
    if len(faces) > 1:
        issues.append(f"❌ Multiple faces detected ({len(faces)}). Only one person should be in the photo")
        return False, issues, None, details
    
    # Check face position
    is_centered, is_good_size, face_found = check_face_position(faces, img_rgb.shape[1], img_rgb.shape[0])
    details['face_centered'] = is_centered
    details['face_good_size'] = is_good_size
    details['face_confidence'] = round(faces[0]['confidence'], 3)
    
    if not is_centered:
        issues.append("❌ Face is not centered in the frame")
    
    if not is_good_size:
        issues.append("❌ Face size is not optimal (too small or too large)")
    
    # Check eye symmetry (basic check for straightness)
    is_symmetric, symmetry_score = check_eye_symmetry(img_rgb, faces)
    details['eye_symmetry_score'] = round(symmetry_score, 3)
    
    if not is_symmetric:
        issues.append("❌ Face appears tilted or asymmetrical")
    
    # Check image quality (blur detection) - ADJUSTED THRESHOLD
    gray = cv2.cvtColor(img_rgb, cv2.COLOR_RGB2GRAY)
    laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
    details['sharpness_score'] = round(laplacian_var, 2)
    
    # Adjusted threshold for blur detection (50 instead of 100)
    if laplacian_var < 50:
        issues.append("❌ Image appears blurry or out of focus")
    
    # Check brightness
    mean_brightness = np.mean(gray)
    details['brightness'] = round(mean_brightness, 2)
    
    if mean_brightness < 50:
        issues.append("❌ Image is too dark")
    elif mean_brightness > 200:
        issues.append("❌ Image is too bright/overexposed")
    
    is_valid = len(issues) == 0
    return is_valid, issues, img_array, details

def remove_background(image):
    """Remove background and replace with white"""
    from rembg import remove
    
    img_byte_arr = io.BytesIO()
    image.save(img_byte_arr, format='PNG')
    img_byte_arr = img_byte_arr.getvalue()
    
    output = remove(img_byte_arr)
    img_no_bg = Image.open(io.BytesIO(output)).convert("RGBA")
    
    white_bg = Image.new("RGBA", img_no_bg.size, "WHITE")
    white_bg.paste(img_no_bg, (0, 0), img_no_bg)
    
    return white_bg.convert("RGB")

def add_face_guide_overlay(image):
    """Add face positioning guide overlay to image"""
    img_array = np.array(image)
    h, w = img_array.shape[:2]
    
    # Create overlay with transparency
    overlay = img_array.copy()
    output = img_array.copy()
    
    # Define oval/ellipse for face position (centered)
    center_x = w // 2
    center_y = h // 2
    # Face oval dimensions (typical face aspect ratio)
    axis_x = int(w * 0.25)  # 25% of width
    axis_y = int(h * 0.35)  # 35% of height
    
    # Draw green oval
    cv2.ellipse(overlay, (center_x, center_y), (axis_x, axis_y), 
                0, 0, 360, (0, 255, 0), 3)
    
    # Add corner markers for alignment
    marker_length = 30
    marker_thickness = 3
    marker_color = (0, 255, 0)
    
    # Top-left corner
    cv2.line(overlay, (center_x - axis_x, center_y - axis_y), 
             (center_x - axis_x + marker_length, center_y - axis_y), marker_color, marker_thickness)
    cv2.line(overlay, (center_x - axis_x, center_y - axis_y), 
             (center_x - axis_x, center_y - axis_y + marker_length), marker_color, marker_thickness)
    
    # Top-right corner
    cv2.line(overlay, (center_x + axis_x, center_y - axis_y), 
             (center_x + axis_x - marker_length, center_y - axis_y), marker_color, marker_thickness)
    cv2.line(overlay, (center_x + axis_x, center_y - axis_y), 
             (center_x + axis_x, center_y - axis_y + marker_length), marker_color, marker_thickness)
    
    # Bottom-left corner
    cv2.line(overlay, (center_x - axis_x, center_y + axis_y), 
             (center_x - axis_x + marker_length, center_y + axis_y), marker_color, marker_thickness)
    cv2.line(overlay, (center_x - axis_x, center_y + axis_y), 
             (center_x - axis_x, center_y + axis_y - marker_length), marker_color, marker_thickness)
    
    # Bottom-right corner
    cv2.line(overlay, (center_x + axis_x, center_y + axis_y), 
             (center_x + axis_x - marker_length, center_y + axis_y), marker_color, marker_thickness)
    cv2.line(overlay, (center_x + axis_x, center_y + axis_y), 
             (center_x + axis_x, center_y + axis_y - marker_length), marker_color, marker_thickness)
    
    # Add text instructions
    font = cv2.FONT_HERSHEY_SIMPLEX
    text = "Align face within oval"
    text_size = cv2.getTextSize(text, font, 0.7, 2)[0]
    text_x = (w - text_size[0]) // 2
    text_y = center_y - axis_y - 20
    
    # Draw text background
    cv2.rectangle(overlay, (text_x - 10, text_y - text_size[1] - 10), 
                  (text_x + text_size[0] + 10, text_y + 10), (0, 0, 0), -1)
    cv2.putText(overlay, text, (text_x, text_y), font, 0.7, (0, 255, 0), 2)
    
    # Blend overlay with original
    alpha = 0.7
    cv2.addWeighted(overlay, alpha, output, 1 - alpha, 0, output)
    
    return Image.fromarray(output)

# Initialize session state
if 'camera_active' not in st.session_state:
    st.session_state.camera_active = False

# Main App
st.title("📸 CV Photo Validator")
st.markdown("**Ensure your CV photo meets professional standards**")

# Create tabs
tab1, tab2 = st.tabs(["📤 Upload Photo", "📷 Take Photo"])

# Tab 1: Upload Photo
with tab1:
    st.header("Upload Your Photo")
    st.markdown("Upload an existing photo to validate it for CV use")
    
    uploaded_file = st.file_uploader(
        "Choose an image file",
        type=['jpg', 'jpeg', 'png'],
        key="upload"
    )
    
    if uploaded_file is not None:
        image = Image.open(uploaded_file)
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.subheader("Original Photo")
            st.image(image, use_container_width=True)
        
        with st.spinner("Analyzing photo..."):
            try:
                is_valid, issues, img_array, details = validate_cv_photo(image)
            except Exception as e:
                st.error(f"Error processing image: {str(e)}")
                import traceback
                st.error(traceback.format_exc())
                st.stop()
        
        with col2:
            st.subheader("Validation Results")
            
            if is_valid:
                st.success("✅ **Photo is suitable for CV!**")
                st.balloons()
                
                with st.expander("Technical Details"):
                    st.json(details)
                
                if st.button("🎨 Remove Background & Make White", key="remove_bg_upload"):
                    with st.spinner("Processing..."):
                        try:
                            white_bg_image = remove_background(image)
                            st.image(white_bg_image, caption="White Background Version", use_container_width=True)
                            
                            buf = io.BytesIO()
                            white_bg_image.save(buf, format="PNG")
                            st.download_button(
                                label="⬇️ Download Photo",
                                data=buf.getvalue(),
                                file_name="cv_photo_white_bg.png",
                                mime="image/png"
                            )
                        except Exception as e:
                            st.error(f"Error removing background: {str(e)}")
            else:
                st.error("❌ **Photo needs improvement**")
                st.markdown("### Issues Found:")
                for issue in issues:
                    st.markdown(f"- {issue}")
                
                with st.expander("Technical Details"):
                    st.json(details)

# Tab 2: Take Photo
with tab2:
    st.header("Take a New Photo")
    st.markdown("""
    ### Instructions:
    1. Click "Open Camera" below to activate your camera
    2. Align your face within the green oval guide
    3. Keep your head straight and centered
    4. Take the photo when ready
    """)
    
    # Camera control
    col_btn1, col_btn2, col_btn3 = st.columns([1, 1, 2])
    
    with col_btn1:
        if st.button("📷 Open Camera", type="primary", disabled=st.session_state.camera_active):
            st.session_state.camera_active = True
    
    with col_btn2:
        if st.button("❌ Close Camera", type="secondary", disabled=not st.session_state.camera_active):
            st.session_state.camera_active = False
    
    # Camera input with overlay
    camera_photo = None
    if st.session_state.camera_active:
        st.info("📸 Camera is active. Align your face within the green oval guide!")
        
        # Show guide overlay example
        guide_col1, guide_col2 = st.columns([1, 2])
        with guide_col1:
            st.markdown("**Position Guide:**")
            st.markdown("✅ Face centered in oval")
            st.markdown("✅ Head straight")
            st.markdown("✅ Good lighting")
        
        camera_photo = st.camera_input("", key="camera", label_visibility="collapsed")
    
    # Process captured photo
    if camera_photo is not None:
        image = Image.open(camera_photo)
        
        # Add face guide overlay to show user where to position
        image_with_guide = add_face_guide_overlay(image)
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.subheader("Captured Photo")
            st.image(image, use_container_width=True)
            
            # Show the guide overlay as reference
            with st.expander("Show Positioning Guide"):
                st.image(image_with_guide, caption="This is how you should position your face", use_container_width=True)
        
        with st.spinner("Analyzing photo..."):
            try:
                is_valid, issues, img_array, details = validate_cv_photo(image)
            except Exception as e:
                st.error(f"Error processing image: {str(e)}")
                import traceback
                st.error(traceback.format_exc())
                st.stop()
        
        with col2:
            st.subheader("Validation Results")
            
            # Show validation results
            if is_valid:
                st.success("🎉 Your photo looks great!")
            else:
                st.warning("⚠️ Photo needs adjustment:")
                for issue in issues:
                    st.markdown(f"- {issue}")
            
            with st.expander("Technical Details"):
                st.json(details)
            
            if is_valid:
                st.subheader("With White Background")
                with st.spinner("Removing background..."):
                    try:
                        white_bg_image = remove_background(image)
                        st.image(white_bg_image, use_container_width=True)
                        
                        buf = io.BytesIO()
                        white_bg_image.save(buf, format="PNG")
                        st.download_button(
                            label="⬇️ Download CV Photo",
                            data=buf.getvalue(),
                            file_name="cv_photo_final.png",
                            mime="image/png",
                            key="download_camera"
                        )
                    except Exception as e:
                        st.error(f"Error removing background: {str(e)}")

# Footer
st.markdown("---")
st.markdown("""
<div style='text-align: center'>
    <p>💡 <strong>Tips for a perfect CV photo:</strong></p>
    <p>✓ Wear professional attire | ✓ Good lighting | ✓ Neutral expression | ✓ Clean background</p>
</div>
""", unsafe_allow_html=True)
