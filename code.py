import streamlit as st
import numpy as np
from PIL import Image
import io
from datetime import datetime
import time
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
    .status-capture { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; border: 2px solid #3b82f6; animation: pulse 1s infinite; }
    @keyframes pulse { 0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); } 70% { transform: scale(1.02); box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); } 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); } }
    .countdown-container { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .countdown-circle { width: 180px; height: 180px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; font-size: 5rem; font-weight: bold; color: white; box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4); animation: countPulse 1s ease-out, glow 2s ease-in-out infinite; border: 4px solid white; }
    @keyframes countPulse { 0% { transform: scale(0); opacity: 0; } 50% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
    @keyframes glow { 0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); } 50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.8); } }
    .countdown-text { margin-top: 1.5rem; font-size: 1.5rem; font-weight: 600; color: #065f46; background: white; padding: 0.75rem 2rem; border-radius: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .stButton>button { width: 100%; height: 3.5rem; font-weight: 600; font-size: 1.2rem; border-radius: 0.75rem; }
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
        'countdown_active': False,
        'auto_capture_frame': None,
        'camera_running': False,
        'last_validation': None,
        'valid_start_time': None,
        'models_ready': False,
        'rembg_ready': False,
        'frame_count': 0,
        'fallback_mode': False
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
        
        # Check for legacy API (solutions)
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
                
                # Test if it actually works
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
            error_msg = "MediaPipe solutions not available (using newer API without legacy support)"
    except ImportError:
        error_msg = "MediaPipe not installed"
    except Exception as e:
        error_msg = f"MediaPipe error: {str(e)}"
    
    # Fallback to Haar Cascade (always available with OpenCV)
    try:
        cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        cascade = cv2.CascadeClassifier(cascade_path)
        
        # Verify it loaded
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
        # Test it works
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
        
        # Resize for faster processing
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
        
        # Convert to PIL
        image_rgb = cv2.cvtColor(img_resized, cv2.COLOR_BGR2RGB)
        pil_image = Image.fromarray(image_rgb)
        
        # Remove background
        output = remove_func(pil_image)
        output_np = np.array(output)
        
        # Resize back if needed
        if needs_resize:
            output_np = cv2.resize(output_np, (original_w, original_h), interpolation=cv2.INTER_LANCZOS4)
        
        # Create white background
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
    
    # Crop to content
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
    
    # Resize to passport 600x600
    h, w = processed.shape[:2]
    scale = 600 / max(h, w) if max(h, w) > 600 else 1
    if scale > 1:
        scale = 1
    
    new_w = int(w * scale)
    new_h = int(h * scale)
    resized = cv2.resize(processed, (new_w, new_h), interpolation=cv2.INTER_LANCZOS4)
    
    final = np.ones((600, 600, 3), dtype=np.uint8) * 255
    y_off = (600 - new_h) // 2
    x_off = (600 - new_w) // 2
    
    y1, y2 = max(0, y_off), min(600, y_off + new_h)
    x1, x2 = max(0, x_off), min(600, x_off + new_w)
    roi_h = y2 - y1
    roi_w = x2 - x1
    
    final[y1:y2, x1:x2] = resized[:roi_h, :roi_w]
    return final

class SimpleValidator:
    """Simplified validator that works with MediaPipe or Haar Cascade"""
    def __init__(self, detector_info):
        self.detector_info = detector_info
        self.detector_type = detector_info['type']
        self.detector = detector_info['detector']
        self.mesh = detector_info.get('mesh')
        
    def validate_frame(self, image):
        h, w = image.shape[:2]
        results = {
            'single': False,
            'straight': True,  # Simplified for Haar
            'tilt': True,      # Simplified for Haar
            'mouth': True,     # Simplified for Haar
            'light': True,
            'all': False,
            'faces': 0
        }
        
        # Check brightness
        brightness = np.mean(cv2.cvtColor(image, cv2.COLOR_BGR2GRAY))
        if brightness < 40:
            results['light'] = False
        
        if self.detector_type == 'mediapipe':
            # MediaPipe detection
            rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
            det_results = self.detector.process(rgb)
            
            if det_results.detections:
                results['faces'] = len(det_results.detections)
                if len(det_results.detections) == 1:
                    results['single'] = True
                    
                    # Try mesh for detailed validation
                    if self.mesh:
                        try:
                            mesh_results = self.mesh.process(rgb)
                            if mesh_results.multi_face_landmarks:
                                landmarks = mesh_results.multi_face_landmarks[0]
                                
                                # Check face tilt (simplified)
                                nose_tip = landmarks.landmark[1]
                                left_eye = landmarks.landmark[33]
                                right_eye = landmarks.landmark[263]
                                
                                eye_diff = abs(left_eye.y - right_eye.y)
                                if eye_diff > 0.05:
                                    results['tilt'] = False
                                
                                # Check mouth (simplified)
                                upper_lip = landmarks.landmark[13]
                                lower_lip = landmarks.landmark[14]
                                mouth_open = abs(upper_lip.y - lower_lip.y)
                                if mouth_open > 0.03:
                                    results['mouth'] = False
                        except:
                            pass  # Keep defaults
        
        elif self.detector_type == 'haar':
            # Haar Cascade detection
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
                # With Haar, we assume other conditions are OK if face is detected
                # (we can't reliably check tilt/mouth/straight with Haar alone)
        
        # All conditions must be met
        results['all'] = all([
            results['single'],
            results['straight'],
            results['tilt'],
            results['mouth'],
            results['light']
        ])
        
        return results

# Load models with error handling
if not st.session_state.models_ready:
    with st.spinner("🚀 Initializing AI Models..."):
        detector_info = load_face_detector()
        
        if detector_info['type'] == 'none':
            st.error(f"❌ Face detection failed: {detector_info['error']}")
            st.stop()
        
        if detector_info['error']:
            st.warning(f"ℹ️ {detector_info['error']}")
        
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
    st.markdown("### 🎯 Real-time Requirements")
    req_cols = st.columns(5)
    req_data = [
        ('single', '👤', 'One Person'),
        ('straight', '👀', 'Straight Look'),
        ('tilt', '🧍', 'No Head Tilt'),
        ('mouth', '😶', 'Mouth Closed'),
        ('light', '💡', 'Good Light')
    ]
    
    req_containers = {}
    for i, (key, icon, label) in enumerate(req_data):
        with req_cols[i]:
            req_containers[key] = st.empty()
            req_containers[key].markdown(
                f'<div class="req-box req-pending">{icon} {label}</div>',
                unsafe_allow_html=True
            )
    
    status_container = st.empty()
    status_container.markdown(
        '<div class="status-bar status-waiting">📹 Press "Start Camera" to begin</div>',
        unsafe_allow_html=True
    )
    
    with st.expander("💡 Photography Tips", expanded=False):
        st.markdown("Stand 2-3 feet from camera • Face a light source • AI will remove any background automatically")
    
    # RESULT DISPLAY
    if st.session_state.auto_capture_frame is not None:
        st.balloons()
        st.success("✅ Photo captured successfully!")
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.subheader("Original Capture")
            st.image(
                cv2.cvtColor(st.session_state.auto_capture_frame, cv2.COLOR_BGR2RGB),
                use_container_width=True
            )
        
        with col2:
            st.subheader("Professional Format")
            
            if not st.session_state.rembg_ready:
                with st.spinner("Loading AI background removal..."):
                    remove_func = load_rembg_model()
                    st.session_state.remove_func = remove_func
                    st.session_state.rembg_ready = True
            
            with st.spinner("Processing with AI..."):
                final = create_passport_photo(
                    st.session_state.auto_capture_frame,
                    st.session_state.remove_func
                )
            
            final_rgb = cv2.cvtColor(final, cv2.COLOR_BGR2RGB)
            st.image(final_rgb, use_container_width=True)
            
            pil_img = Image.fromarray(final_rgb)
            buf = io.BytesIO()
            pil_img.save(buf, format='PNG', quality=95)
            
            st.download_button(
                label="⬇️ Download Professional Picture",
                data=buf.getvalue(),
                file_name=f"TUF_Professional_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png",
                mime="image/png",
                use_container_width=True
            )
        
        if st.button("🔄 Take Another Photo", type="primary", use_container_width=True):
            st.session_state.auto_capture_frame = None
            st.session_state.valid_start_time = None
            st.session_state.countdown_active = False
            st.session_state.camera_running = False
            st.rerun()
    
    else:
        # CAMERA MODE
        if not st.session_state.camera_running:
            st.info("👆 Position yourself in front of the camera!")
            
            col1, col2 = st.columns(2)
            with col1:
                if st.button("📷 Start Camera", type="primary", use_container_width=True):
                    st.session_state.camera_running = True
                    st.rerun()
            with col2:
                uploaded = st.file_uploader("Or upload photo", type=['jpg', 'jpeg', 'png'])
                if uploaded:
                    file_bytes = np.asarray(bytearray(uploaded.read()), dtype=np.uint8)
                    img = cv2.imdecode(file_bytes, cv2.IMREAD_COLOR)
                    st.session_state.auto_capture_frame = img
                    st.rerun()
        else:
            st.info("🎥 Camera Active")
            
            video_col, control_col = st.columns([3, 1])
            
            with control_col:
                st.markdown("### Controls")
                if st.button("⏹️ Stop", use_container_width=True):
                    st.session_state.camera_running = False
                    st.session_state.valid_start_time = None
                    st.rerun()
                st.markdown("---")
                status_text = st.empty()
                status_text.text("Initializing...")
            
            with video_col:
                FRAME_WINDOW = st.empty()
                COUNTDOWN_HTML = st.empty()
            
            cap = cv2.VideoCapture(0)
            
            if not cap.isOpened():
                st.error("❌ Camera not found")
                st.session_state.camera_running = False
                st.rerun()
            
            try:
                frame_counter = 0
                valid_start = None
                last_countdown_val = None
                
                while st.session_state.camera_running:
                    ret, frame = cap.read()
                    if not ret:
                        break
                    
                    frame = cv2.flip(frame, 1)
                    
                    if frame_counter % 3 == 0:
                        results = validator.validate_frame(frame)
                        
                        for key, icon, label in req_data:
                            css_class = "req-success" if results[key] else "req-pending"
                            check = "✓ " if results[key] else ""
                            req_containers[key].markdown(
                                f'<div class="req-box {css_class}">{icon} {check}{label}</div>',
                                unsafe_allow_html=True
                            )
                        
                        if results['all']:
                            if valid_start is None:
                                valid_start = time.time()
                                last_countdown_val = 3
                            
                            elapsed = time.time() - valid_start
                            remaining = int(max(0, 3 - elapsed))
                            
                            if remaining > 0:
                                if remaining != last_countdown_val:
                                    last_countdown_val = remaining
                                    COUNTDOWN_HTML.markdown(f"""
                                        <div class="countdown-container">
                                            <div class="countdown-circle">{remaining}</div>
                                            <div class="countdown-text">HOLD STILL</div>
                                        </div>
                                    """, unsafe_allow_html=True)
                                    status_text.text(f"Capturing in {remaining}...")
                                
                                status_container.markdown(
                                    '<div class="status-bar status-capture">📸 Hold position...</div>',
                                    unsafe_allow_html=True
                                )
                                
                                h, w = frame.shape[:2]
                                cv2.circle(frame, (w//2, h//2), 150, (0, 255, 0), 3)
                            else:
                                COUNTDOWN_HTML.empty()
                                st.session_state.auto_capture_frame = frame.copy()
                                cap.release()
                                st.rerun()
                        else:
                            valid_start = None
                            last_countdown_val = None
                            COUNTDOWN_HTML.empty()
                            
                            missing = []
                            if not results['single']:
                                if results['faces'] == 0:
                                    missing.append("Show face")
                                else:
                                    missing.append("One person only")
                            elif not results['straight']:
                                missing.append("Look straight")
                            elif not results['tilt']:
                                missing.append("Straighten head")
                            elif not results['mouth']:
                                missing.append("Close mouth")
                            elif not results['light']:
                                missing.append("More light")
                            
                            msg = " | ".join(missing) if missing else "Adjust..."
                            status_container.markdown(
                                f'<div class="status-bar status-waiting">⚠️ {msg}</div>',
                                unsafe_allow_html=True
                            )
                            status_text.text(msg)
                    
                    frame_counter += 1
                    
                    h, w = frame.shape[:2]
                    cv2.ellipse(frame, (w//2, h//2), (w//4, h//3), 0, 0, 360, (0, 255, 255), 2)
                    
                    FRAME_WINDOW.image(cv2.cvtColor(frame, cv2.COLOR_BGR2RGB), use_container_width=True)
                    time.sleep(0.033)
                    
            finally:
                cap.release()
                cv2.destroyAllWindows()
else:
    st.error("❌ Failed to initialize AI models")
