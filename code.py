import streamlit as st
import numpy as np
from PIL import Image
import io
from datetime import datetime
import warnings
warnings.filterwarnings('ignore')

# Page configuration
st.set_page_config(
    page_title="TUF Smart Picture Validator",
    page_icon="📸",
    layout="wide",
    initial_sidebar_state="collapsed"
)

# Check for OpenCV
try:
    import cv2
    CV2_AVAILABLE = True
except ImportError:
    CV2_AVAILABLE = False
    st.error("⚠️ OpenCV not available")

# CSS Styles
st.markdown("""
<style>
    .main-header { font-size: 2.2rem; font-weight: bold; color: #1f2937; text-align: center; margin-bottom: 0.5rem; }
    .sub-header { font-size: 1.1rem; color: #6b7280; text-align: center; margin-bottom: 2rem; }
    .req-box { padding: 1rem 0.5rem; margin: 0.5rem 0; border-radius: 0.75rem; text-align: center; font-weight: 600; transition: all 0.3s ease; border: 2px solid; font-size: 0.9rem; }
    .req-pending { background-color: #f3f4f6; color: #6b7280; border-color: #d1d5db; opacity: 0.7; }
    .req-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-color: #10b981; transform: scale(1.05); box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3); }
    .status-bar { padding: 1.5rem; border-radius: 1rem; text-align: center; font-size: 1.25rem; font-weight: 600; margin: 1rem 0; transition: all 0.3s ease; }
    .status-waiting { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; border: 2px solid #f59e0b; }
    .status-success { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border: 2px solid #10b981; }
    .capture-btn { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; font-weight: 600; }
    .camera-container { border: 2px solid #e5e7eb; border-radius: 1rem; padding: 1rem; background: #f9fafb; }
</style>
""", unsafe_allow_html=True)

if not CV2_AVAILABLE:
    st.error("OpenCV is required but not installed.")
    st.stop()

# Initialize session state
if 'init' not in st.session_state:
    st.session_state.update({
        'init': True,
        'captured_image': None,
        'validation_result': None,
        'models_ready': False,
        'rembg_ready': False,
        'show_camera': False
    })

st.markdown('<h1 class="main-header">The University of Faisalabad</h1>', unsafe_allow_html=True)
st.markdown('<h2 class="sub-header">Placement Bureau - Professional Picture Validator</h2>', unsafe_allow_html=True)

@st.cache_resource(show_spinner=False)
def load_face_detector():
    """Load face detector - uses Haar Cascade as reliable fallback"""
    detector_type = None
    detector = None
    mesh = None
    error_msg = None
    
    # Try MediaPipe first
    try:
        import mediapipe as mp
        
        if hasattr(mp, 'solutions') and hasattr(mp.solutions, 'face_detection'):
            try:
                mp_face_detection = mp.solutions.face_detection
                mp_face_mesh = mp.solutions.face_mesh
                
                face_detection = mp_face_detection.FaceDetection(
                    model_selection=1, 
                    min_detection_confidence=0.5
                )
                face_mesh = mp_face_mesh.FaceMesh(
                    max_num_faces=1, 
                    refine_landmarks=True, 
                    min_detection_confidence=0.5,
                    min_tracking_confidence=0.5
                )
                
                test_img = np.zeros((100, 100, 3), dtype=np.uint8)
                test_result = face_detection.process(test_img)
                
                return {
                    'type': 'mediapipe',
                    'detector': face_detection,
                    'mesh': face_mesh,
                    'error': None
                }
            except Exception as e:
                error_msg = f"MediaPipe solutions failed: {str(e)}"
        else:
            error_msg = "MediaPipe solutions not available"
    except ImportError:
        error_msg = "MediaPipe not installed"
    except Exception as e:
        error_msg = f"MediaPipe error: {str(e)}"
    
    # Fallback to Haar Cascade
    try:
        cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        cascade = cv2.CascadeClassifier(cascade_path)
        
        if cascade.empty():
            raise Exception("Haar Cascade failed to load")
        
        return {
            'type': 'haar',
            'detector': cascade,
            'mesh': None,
            'error': error_msg
        }
    except Exception as e:
        return {
            'type': 'none',
            'detector': None,
            'mesh': None,
            'error': f"All detectors failed. Haar error: {str(e)}, MediaPipe: {error_msg}"
        }

@st.cache_resource(show_spinner=False)
def load_rembg_model():
    try:
        from rembg import remove
        import onnxruntime as ort
        ort.set_default_logger_severity(3)
        test_img = Image.new('RGB', (10, 10), color='white')
        _ = remove(test_img)
        return remove
    except Exception as e:
        st.warning(f"⚠️ Background removal unavailable: {e}")
        return None

def fast_remove_background(image, remove_func):
    if remove_func is None:
        return image
        
    try:
        original_h, original_w = image.shape[:2]
        max_size = 1024
        if max(original_h, original_w) > max_size:
            scale = max_size / max(original_h, original_w)
            new_w = int(original_w * scale)
            new_h = int(original_h * scale)
            img_resized = cv2.resize(image, (new_w, new_h))
            needs_resize = True
        else:
            img_resized = image
            needs_resize = False
        
        image_rgb = cv2.cvtColor(img_resized, cv2.COLOR_BGR2RGB)
        pil_image = Image.fromarray(image_rgb)
        output = remove_func(pil_image)
        output_np = np.array(output)
        
        if needs_resize:
            output_np = cv2.resize(output_np, (original_w, original_h), interpolation=cv2.INTER_LANCZOS4)
        
        if output_np.shape[2] == 4:
            rgb = output_np[:, :, :3].astype(np.float32)
            alpha = output_np[:, :, 3].astype(np.float32)
            alpha[alpha < 40] = 0
            alpha[alpha > 240] = 255
            alpha = cv2.GaussianBlur(alpha, (5, 5), 0)
            alpha = alpha / 255.0
            alpha = alpha[:, :, None]
            white_bg = np.ones_like(rgb) * 255
            blended = rgb * alpha + white_bg * (1 - alpha)
            blended = blended.astype(np.uint8)
            return cv2.cvtColor(blended, cv2.COLOR_RGB2BGR)
        else:
            return cv2.cvtColor(output_np, cv2.COLOR_RGB2BGR)
            
    except Exception as e:
        st.warning(f"Background removal failed: {e}")
        return image

def create_passport_photo(image, remove_func):
    processed = fast_remove_background(image, remove_func)
    gray = cv2.cvtColor(processed, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 240, 255, cv2.THRESH_BINARY_INV)
    coords = cv2.findNonZero(thresh)
    
    if coords is not None:
        x, y, w, h = cv2.boundingRect(coords)
        padding = int(min(w, h) * 0.1)
        x = max(0, x - padding)
        y = max(0, y - padding)
        w = min(processed.shape[1] - x, w + 2*padding)
        h = min(processed.shape[0] - y, h + 2*padding)
        processed = processed[y:y+h, x:x+w]
    
    h, w = processed.shape[:2]
    scale = 600 / max(h, w) if max(h, w) > 600 else 1
    if scale < 1:
        new_w = int(w * scale)
        new_h = int(h * scale)
        resized = cv2.resize(processed, (new_w, new_h), interpolation=cv2.INTER_LANCZOS4)
    else:
        resized = processed
    
    final = np.ones((600, 600, 3), dtype=np.uint8) * 255
    y_off = (600 - resized.shape[0]) // 2
    x_off = (600 - resized.shape[1]) // 2
    
    y1, y2 = max(0, y_off), min(600, y_off + resized.shape[0])
    x1, x2 = max(0, x_off), min(600, x_off + resized.shape[1])
    roi_h = y2 - y1
    roi_w = x2 - x1
    
    if roi_h > 0 and roi_w > 0:
        final[y1:y2, x1:x2] = resized[:roi_h, :roi_w]
    return final

class SimpleValidator:
    def __init__(self, detector_info):
        self.detector_info = detector_info
        self.detector_type = detector_info['type']
        self.detector = detector_info['detector']
        self.mesh = detector_info.get('mesh')
        
    def validate_frame(self, image):
        h, w = image.shape[:2]
        results = {
            'single': False,
            'straight': True,
            'tilt': True,
            'mouth': True,
            'light': True,
            'all': False,
            'faces': 0,
            'message': ''
        }
        
        brightness = np.mean(cv2.cvtColor(image, cv2.COLOR_BGR2GRAY))
        if brightness < 40:
            results['light'] = False
            results['message'] = 'More light needed'
        
        if self.detector_type == 'mediapipe':
            rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
            det_results = self.detector.process(rgb)
            
            if det_results.detections:
                results['faces'] = len(det_results.detections)
                if len(det_results.detections) == 1:
                    results['single'] = True
                    
                    if self.mesh:
                        try:
                            mesh_results = self.mesh.process(rgb)
                            if mesh_results.multi_face_landmarks:
                                landmarks = mesh_results.multi_face_landmarks[0]
                                nose_tip = landmarks.landmark[1]
                                left_eye = landmarks.landmark[33]
                                right_eye = landmarks.landmark[263]
                                
                                eye_diff = abs(left_eye.y - right_eye.y)
                                if eye_diff > 0.05:
                                    results['tilt'] = False
                                    results['message'] = 'Straighten head'
                                
                                upper_lip = landmarks.landmark[13]
                                lower_lip = landmarks.landmark[14]
                                mouth_open = abs(upper_lip.y - lower_lip.y)
                                if mouth_open > 0.03:
                                    results['mouth'] = False
                                    results['message'] = 'Close mouth'
                        except:
                            pass
                else:
                    results['message'] = 'One person only'
            else:
                results['message'] = 'No face detected'
        
        elif self.detector_type == 'haar':
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            faces = self.detector.detectMultiScale(
                gray,
                scaleFactor=1.1,
                minNeighbors=5,
                minSize=(100, 100)
            )
            results['faces'] = len(faces)
            if len(faces) == 1:
                results['single'] = True
            elif len(faces) > 1:
                results['message'] = 'One person only'
            else:
                results['message'] = 'No face detected'
        
        results['all'] = all([
            results['single'],
            results['straight'],
            results['tilt'],
            results['mouth'],
            results['light']
        ])
        
        return results

# Load models
if not st.session_state.models_ready:
    with st.spinner("🚀 Initializing AI Models..."):
        detector_info = load_face_detector()
        
        if detector_info['type'] == 'none':
            st.error(f"❌ Face detection failed: {detector_info['error']}")
            st.stop()
        
        if detector_info['error']:
            st.info(f"ℹ️ Using fallback: {detector_info['error']}")
        
        st.session_state.detector_info = detector_info
        st.session_state.validator = SimpleValidator(detector_info)
        st.session_state.models_ready = True
        
        detector_name = {
            'mediapipe': 'MediaPipe Face Detection',
            'haar': 'OpenCV Haar Cascade'
        }.get(detector_info['type'], 'Unknown')
        
        st.success(f"✅ Loaded: {detector_name}")

if st.session_state.models_ready:
    validator = st.session_state.validator
    
    # Requirements display
    st.markdown("### 🎯 Validation Checklist")
    req_cols = st.columns(5)
    req_data = [
        ('single', '👤', 'One Person'),
        ('straight', '👀', 'Straight Look'),
        ('tilt', '🧍', 'No Head Tilt'),
        ('mouth', '😶', 'Mouth Closed'),
        ('light', '💡', 'Good Light')
    ]
    
    # Create placeholders for dynamic updates
    req_placeholders = {}
    for i, (key, icon, label) in enumerate(req_data):
        with req_cols[i]:
            req_placeholders[key] = st.empty()
            req_placeholders[key].markdown(
                f'<div class="req-box req-pending">{icon} {label}</div>',
                unsafe_allow_html=True
            )
    
    status_container = st.empty()
    
    with st.expander("💡 Photography Tips", expanded=False):
        st.markdown("""
        - Stand 2-3 feet from camera
        - Face a light source (window/lamp)
        - Keep head straight and look at camera
        - Close mouth naturally
        - Plain background preferred (AI will remove it anyway)
        """)

    # Main content area
    if st.session_state.captured_image is not None:
        # SHOW RESULTS
        st.success("✅ Photo captured!")
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.subheader("📸 Original Capture")
            st.image(st.session_state.captured_image, use_container_width=True)
            
            # Show validation results
            results = st.session_state.validation_result
            if results:
                st.markdown("#### Validation Results")
                if results['all']:
                    st.success("✅ Perfect! All requirements met")
                else:
                    if not results['single']:
                        if results['faces'] == 0:
                            st.error("❌ No face detected")
                        else:
                            st.error(f"❌ Multiple faces detected ({results['faces']})")
                    elif not results['tilt']:
                        st.warning("⚠️ Head tilt detected")
                    elif not results['mouth']:
                        st.warning("⚠️ Mouth appears open")
                    elif not results['light']:
                        st.warning("⚠️ Too dark")
        
        with col2:
            st.subheader("🎨 Professional Format")
            
            # Convert PIL to CV2 for processing
            img_array = np.array(st.session_state.captured_image)
            img_cv = cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
            
            if not st.session_state.rembg_ready:
                with st.spinner("Loading AI background removal..."):
                    remove_func = load_rembg_model()
                    st.session_state.remove_func = remove_func
                    st.session_state.rembg_ready = True
            
            with st.spinner("Processing with AI..."):
                final = create_passport_photo(img_cv, st.session_state.remove_func)
            
            final_rgb = cv2.cvtColor(final, cv2.COLOR_BGR2RGB)
            st.image(final_rgb, use_container_width=True)
            
            # Download button
            final_pil = Image.fromarray(final_rgb)
            buf = io.BytesIO()
            final_pil.save(buf, format='PNG', quality=95)
            
            st.download_button(
                label="⬇️ Download Professional Picture",
                data=buf.getvalue(),
                file_name=f"TUF_Professional_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png",
                mime="image/png",
                use_container_width=True
            )
        
        if st.button("🔄 Take Another Photo", type="primary", use_container_width=True):
            st.session_state.captured_image = None
            st.session_state.validation_result = None
            st.rerun()
    
    else:
        # CAPTURE MODE
        status_container.markdown(
            '<div class="status-bar status-waiting">📹 Take photo using camera or upload</div>',
            unsafe_allow_html=True
        )
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.markdown("#### 📱 Camera Capture")
            st.markdown('<div class="camera-container">', unsafe_allow_html=True)
            
            # Use native camera input
            camera_photo = st.camera_input(
                "Take a photo",
                label_visibility="collapsed",
                help="Click 'Take Photo' to capture"
            )
            
            st.markdown('</div>', unsafe_allow_html=True)
            
            if camera_photo is not None:
                # Process camera input
                image = Image.open(camera_photo)
                img_array = np.array(image)
                
                # Convert to CV2 format for validation
                if len(img_array.shape) == 3 and img_array.shape[2] == 3:
                    img_cv = cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
                else:
                    img_cv = cv2.cvtColor(img_array, cv2.COLOR_RGBA2BGR)
                
                # Validate
                results = validator.validate_frame(img_cv)
                
                # Update UI indicators
                for key, icon, label in req_data:
                    css_class = "req-success" if results[key] else "req-pending"
                    check = "✓ " if results[key] else ""
                    req_placeholders[key].markdown(
                        f'<div class="req-box {css_class}">{icon} {check}{label}</div>',
                        unsafe_allow_html=True
                    )
                
                if results['all']:
                    status_container.markdown(
                        '<div class="status-bar status-success">✅ Perfect! Processing...</div>',
                        unsafe_allow_html=True
                    )
                    st.session_state.captured_image = image
                    st.session_state.validation_result = results
                    st.rerun()
                else:
                    status_container.markdown(
                        f'<div class="status-bar status-waiting">⚠️ {results["message"]} - Try again</div>',
                        unsafe_allow_html=True
                    )
                    # Show preview of what was captured with error
                    st.error(f"❌ {results['message']}")
                    st.image(image, caption="Retake photo to fix issues", use_container_width=True)
        
        with col2:
            st.markdown("#### 📁 Upload Photo")
            uploaded = st.file_uploader("Choose image", type=['jpg', 'jpeg', 'png'])
            
            if uploaded:
                image = Image.open(uploaded)
                
                # Convert to CV2
                img_array = np.array(image)
                if len(img_array.shape) == 3:
                    if img_array.shape[2] == 3:
                        img_cv = cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
                    elif img_array.shape[2] == 4:
                        img_cv = cv2.cvtColor(img_array, cv2.COLOR_RGBA2BGR)
                    else:
                        st.error("Invalid image format")
                        st.stop()
                else:
                    # Grayscale
                    img_cv = cv2.cvtColor(img_array, cv2.COLOR_GRAY2BGR)
                
                # Validate
                results = validator.validate_frame(img_cv)
                
                # Update UI indicators
                for key, icon, label in req_data:
                    css_class = "req-success" if results[key] else "req-pending"
                    check = "✓ " if results[key] else ""
                    req_placeholders[key].markdown(
                        f'<div class="req-box {css_class}">{icon} {check}{label}</div>',
                        unsafe_allow_html=True
                    )
                
                if results['all']:
                    status_container.markdown(
                        '<div class="status-bar status-success">✅ Valid photo uploaded</div>',
                        unsafe_allow_html=True
                    )
                    st.session_state.captured_image = image
                    st.session_state.validation_result = results
                    st.rerun()
                else:
                    status_container.markdown(
                        f'<div class="status-bar status-waiting">⚠️ Validation failed</div>',
                        unsafe_allow_html=True
                    )
                    st.error(f"❌ {results['message']}")
                    st.image(image, caption="This image doesn't meet requirements", use_container_width=True)

else:
    st.error("❌ Failed to initialize AI models")
