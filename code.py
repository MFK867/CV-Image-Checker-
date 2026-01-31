import streamlit as st
import cv2
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

# Enhanced CSS with professional countdown
st.markdown("""
<style>
    .main-header {
        font-size: 2.2rem;
        font-weight: bold;
        color: #1f2937;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    .sub-header {
        font-size: 1.1rem;
        color: #6b7280;
        text-align: center;
        margin-bottom: 2rem;
    }
    .req-box {
        padding: 1rem 0.5rem;
        margin: 0.5rem 0;
        border-radius: 0.75rem;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid;
        font-size: 0.9rem;
    }
    .req-pending { 
        background-color: #f3f4f6; 
        color: #6b7280; 
        border-color: #d1d5db;
        opacity: 0.7;
    }
    .req-success { 
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white; 
        border-color: #10b981;
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
    }
    .status-bar {
        padding: 1.5rem;
        border-radius: 1rem;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 1rem 0;
        transition: all 0.3s ease;
    }
    .status-waiting { 
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 2px solid #f59e0b;
    }
    .status-ready { 
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border: 2px solid #10b981;
    }
    .status-capture { 
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border: 2px solid #3b82f6;
        animation: pulse 1s infinite;
    }
    @keyframes pulse { 
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        70% { transform: scale(1.02); box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    
    /* Professional Countdown Overlay */
    .countdown-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .countdown-circle {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        font-weight: bold;
        color: white;
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4);
        animation: countPulse 1s ease-out, glow 2s ease-in-out infinite;
        border: 4px solid white;
    }
    @keyframes countPulse { 
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes glow {
        0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.8); }
    }
    .countdown-text {
        margin-top: 1.5rem;
        font-size: 1.5rem;
        font-weight: 600;
        color: #065f46;
        background: white;
        padding: 0.75rem 2rem;
        border-radius: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    /* Progress steps */
    .progress-step {
        padding: 1rem;
        margin: 0.5rem 0;
        border-radius: 0.5rem;
        background: #f3f4f6;
        border-left: 4px solid #d1d5db;
        transition: all 0.3s;
    }
    .progress-step.active {
        background: #dbeafe;
        border-left-color: #3b82f6;
        animation: slideIn 0.3s ease;
    }
    .progress-step.complete {
        background: #d1fae5;
        border-left-color: #10b981;
    }
    @keyframes slideIn {
        from { transform: translateX(-10px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .stButton>button { 
        width: 100%; 
        height: 3.5rem; 
        font-weight: 600; 
        font-size: 1.2rem;
        border-radius: 0.75rem;
    }
</style>
""", unsafe_allow_html=True)

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
        'frame_count': 0
    })

st.markdown('<h1 class="main-header">The University of Faisalabad</h1>', unsafe_allow_html=True)
st.markdown('<h2 class="sub-header">Placement Bureau - Professional Picture Validator</h2>', unsafe_allow_html=True)

@st.cache_resource(show_spinner=False)
def load_mediapipe():
    try:
        import mediapipe as mp
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
        return face_detection, face_mesh
    except Exception as e:
        st.error(f"Error loading MediaPipe: {e}")
        return None, None

@st.cache_resource(show_spinner=False)
def load_rembg_model():
    try:
        from rembg import remove
        import onnxruntime as ort
        # Set ONNX to use CPU for faster loading
        ort.set_default_logger_severity(3)
        return remove
    except Exception as e:
        st.error(f"rembg not available: {e}")
        return None

def fast_remove_background(image, remove_func):
    """
    Optimized background removal with resizing for speed
    """
    try:
        original_h, original_w = image.shape[:2]
        
        # Resize for faster processing (rembg is slow on large images)
        # Process at max 1024px for speed, then resize back
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
            
            # Clean alpha
            alpha[alpha < 40] = 0
            alpha[alpha > 240] = 255
            
            # Smooth edges
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
        print(f"Error: {e}")
        return image

def create_passport_photo(image, remove_func):
    """
    Create professional passport photo with progress tracking
    """
    # Step 1: Remove background
    processed = fast_remove_background(image, remove_func)
    
    # Step 2: Crop to content
    gray = cv2.cvtColor(processed, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 240, 255, cv2.THRESH_BINARY_INV)
    coords = cv2.findNonZero(thresh)
    
    if coords is not None:
        x, y, w, h = cv2.boundingRect(coords)
        padding = int(min(w, h) * 0.1)  # 10% padding
        x = max(0, x - padding)
        y = max(0, y - padding)
        w = min(processed.shape[1] - x, w + 2*padding)
        h = min(processed.shape[0] - y, h + 2*padding)
        processed = processed[y:y+h, x:x+w]
    
    # Step 3: Resize to passport 600x600 with head ~70% of height
    h, w = processed.shape[:2]
    # Calculate to make face ~420px (70% of 600)
    scale = 600 / max(h, w) if max(h, w) > 600 else 1
    if scale > 1:  # Don't upscale small images
        scale = 1
    
    new_w = int(w * scale)
    new_h = int(h * scale)
    resized = cv2.resize(processed, (new_w, new_h), interpolation=cv2.INTER_LANCZOS4)
    
    # Create canvas
    final = np.ones((600, 600, 3), dtype=np.uint8) * 255
    y_off = (600 - new_h) // 2
    x_off = (600 - new_w) // 2
    
    # Ensure bounds
    y1, y2 = max(0, y_off), min(600, y_off + new_h)
    x1, x2 = max(0, x_off), min(600, x_off + new_w)
    roi_h = y2 - y1
    roi_w = x2 - x1
    
    final[y1:y2, x1:x2] = resized[:roi_h, :roi_w]
    return final

class Validator:
    def __init__(self, face_detection, face_mesh):
        self.face_detection = face_detection
        self.face_mesh = face_mesh
        self.MAX_TILT = 15
        self.MAX_YAW = 20
        self.MAX_PITCH = 25
        self.MAX_MOUTH = 0.05
        
    def get_head_pose(self, landmarks, w, h):
        try:
            model_points = np.array([
                (0.0, 0.0, 0.0), (0.0, -330.0, -65.0), (-225.0, 170.0, -135.0),
                (225.0, 170.0, -135.0), (-150.0, -150.0, -125.0), (150.0, -150.0, -125.0)
            ])
            image_points = np.array([
                (landmarks[1].x * w, landmarks[1].y * h), (landmarks[152].x * w, landmarks[152].y * h),
                (landmarks[263].x * w, landmarks[263].y * h), (landmarks[33].x * w, landmarks[33].y * h),
                (landmarks[287].x * w, landmarks[287].y * h), (landmarks[57].x * w, landmarks[57].y * h)
            ], dtype="double")
            
            focal_length = w
            center = (w / 2, h / 2)
            camera_matrix = np.array([
                [focal_length, 0, center[0]], [0, focal_length, center[1]], [0, 0, 1]
            ], dtype="double")
            dist_coeffs = np.zeros((4, 1))
            
            success, rotation_vector, _ = cv2.solvePnP(
                model_points, image_points, camera_matrix, dist_coeffs
            )
            
            if success:
                rmat, _ = cv2.Rodrigues(rotation_vector)
                angles, _, _, _, _, _ = cv2.RQDecomp3x3(rmat)
                return angles[0], angles[1], angles[2]
            return 0, 0, 0
        except:
            return 0, 0, 0
    
    def get_mouth_openness(self, landmarks):
        upper = landmarks[13].y
        lower = landmarks[14].y
        left = landmarks[61].x
        right = landmarks[291].x
        width = abs(right - left)
        height = abs(lower - upper)
        return height / width if width > 0 else 0
    
    def validate_frame(self, image):
        h, w = image.shape[:2]
        rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        
        results = {
            'single': False, 'straight': False, 'tilt': False,
            'mouth': False, 'light': True, 'all': False, 'faces': 0
        }
        
        # Check brightness
        brightness = np.mean(cv2.cvtColor(image, cv2.COLOR_BGR2GRAY))
        if brightness < 50:
            results['light'] = False
        
        # Face detection
        det_results = self.face_detection.process(rgb)
        
        if det_results.detections:
            results['faces'] = len(det_results.detections)
            if len(det_results.detections) == 1:
                results['single'] = True
                
                mesh_results = self.face_mesh.process(rgb)
                if mesh_results.multi_face_landmarks:
                    landmarks = mesh_results.multi_face_landmarks[0].landmark
                    pitch, yaw, roll = self.get_head_pose(landmarks, w, h)
                    
                    if abs(yaw) < self.MAX_YAW:
                        results['straight'] = True
                    if abs(roll) < self.MAX_TILT and abs(pitch) < self.MAX_PITCH:
                        results['tilt'] = True
                    
                    if self.get_mouth_openness(landmarks) < self.MAX_MOUTH:
                        results['mouth'] = True
        
        results['all'] = all([results['single'], results['straight'], 
                             results['tilt'], results['mouth'], results['light']])
        return results

# Load models
if not st.session_state.models_ready:
    with st.spinner("🚀 Initializing AI Models..."):
        face_detection, face_mesh = load_mediapipe()
        if face_detection is not None:
            st.session_state.face_detection = face_detection
            st.session_state.face_mesh = face_mesh
            st.session_state.models_ready = True

if st.session_state.models_ready:
    validator = Validator(st.session_state.face_detection, st.session_state.face_mesh)
    
    # Requirements display
    st.markdown("### 🎯 Real-time Requirements")
    req_cols = st.columns(5)
    req_data = [
        ('single', '👤', 'One Person'), ('straight', '👀', 'Straight Look'),
        ('tilt', '🧍', 'No Head Tilt'), ('mouth', '😶', 'Mouth Closed'),
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
            st.image(cv2.cvtColor(st.session_state.auto_capture_frame, cv2.COLOR_BGR2RGB), 
                    use_container_width=True)  # Fixed deprecation
        
        with col2:
            st.subheader("Professional Format")
            
            # Load rembg only once
            if not st.session_state.rembg_ready:
                with st.spinner("Loading AI background removal..."):
                    remove_func = load_rembg_model()
                    if remove_func:
                        st.session_state.remove_func = remove_func
                        st.session_state.rembg_ready = True
            
            # Process with progress tracking
            progress_placeholder = st.empty()
            steps = ["Removing background...", "Cropping to content...", "Resizing to passport format..."]
            
            # Use a simple spinner for the whole process since we can't easily stream steps
            with st.spinner("Processing with AI... This may take 10-20 seconds"):
                final = create_passport_photo(
                    st.session_state.auto_capture_frame, 
                    st.session_state.remove_func
                )
            
            final_rgb = cv2.cvtColor(final, cv2.COLOR_BGR2RGB)
            st.image(final_rgb, use_container_width=True)  # Fixed deprecation
            
            # Download
            pil_img = Image.fromarray(final_rgb)
            buf = io.BytesIO()
            pil_img.save(buf, format='PNG', quality=95)
            
            st.download_button(
                label="⬇️ Download Professional Picture",
                data=buf.getvalue(),
                file_name=f"TUF_Professional_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png",
                mime="image/png",
                use_container_width=True  # Fixed deprecation
            )
        
        if st.button("🔄 Take Another Photo", type="primary", use_container_width=True):  # Fixed deprecation
            st.session_state.auto_capture_frame = None
            st.session_state.valid_start_time = None
            st.session_state.countdown_active = False
            st.session_state.camera_running = False
            st.rerun()
    
    else:
        # CAMERA MODE
        if not st.session_state.camera_running:
            st.info("👆 Position yourself in front of the camera. When all boxes turn green, hold still for auto-capture!")
            
            col1, col2 = st.columns(2)
            with col1:
                if st.button("📷 Start Camera", type="primary", use_container_width=True):  # Fixed deprecation
                    st.session_state.camera_running = True
                    st.rerun()
            with col2:
                uploaded = st.file_uploader("Or upload photo", type=['jpg','jpeg','png'])
                if uploaded:
                    file_bytes = np.asarray(bytearray(uploaded.read()), dtype=np.uint8)
                    img = cv2.imdecode(file_bytes, cv2.IMREAD_COLOR)
                    st.session_state.auto_capture_frame = img
                    st.rerun()
        else:
            st.info("🎥 Camera Active - Adjust your position")
            
            video_col, control_col = st.columns([3, 1])
            
            with control_col:
                st.markdown("### Controls")
                if st.button("⏹️ Stop", use_container_width=True):  # Fixed deprecation
                    st.session_state.camera_running = False
                    st.session_state.valid_start_time = None
                    st.rerun()
                st.markdown("---")
                status_text = st.empty()
                status_text.text("Initializing...")
            
            with video_col:
                FRAME_WINDOW = st.empty()
                # Hidden placeholder for countdown HTML
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
                    
                    frame = cv2.flip(frame, 1)  # Mirror
                    
                    # Process every 3rd frame
                    if frame_counter % 3 == 0:
                        results = validator.validate_frame(frame)
                        
                        # Update requirements
                        for key, icon, label in req_data:
                            css_class = "req-success" if results[key] else "req-pending"
                            check = "✓ " if results[key] else ""
                            req_containers[key].markdown(
                                f'<div class="req-box {css_class}">{icon} {check}{label}</div>',
                                unsafe_allow_html=True
                            )
                        
                        # Auto-capture logic
                        if results['all']:
                            if valid_start is None:
                                valid_start = time.time()
                                last_countdown_val = 3
                            
                            elapsed = time.time() - valid_start
                            remaining = int(max(0, 3 - elapsed))
                            
                            if remaining > 0:
                                # Only update HTML when value changes to avoid flicker
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
                                    f'<div class="status-bar status-capture">📸 Hold position...</div>',
                                    unsafe_allow_html=True
                                )
                                
                                # Draw subtle indicator on frame too
                                h, w = frame.shape[:2]
                                cv2.circle(frame, (w//2, h//2), 150, (0, 255, 0), 3)
                                
                            else:
                                # CAPTURE!
                                COUNTDOWN_HTML.empty()
                                st.session_state.auto_capture_frame = frame.copy()
                                cap.release()
                                st.rerun()
                        else:
                            valid_start = None
                            last_countdown_val = None
                            COUNTDOWN_HTML.empty()
                            
                            missing = []
                            if not results['single']: missing.append("Show face")
                            elif not results['straight']: missing.append("Look straight")
                            elif not results['tilt']: missing.append("Straighten head")
                            elif not results['mouth']: missing.append("Close mouth")
                            elif not results['light']: missing.append("More light")
                            
                            msg = " | ".join(missing) if missing else "Adjust..."
                            status_container.markdown(
                                f'<div class="status-bar status-waiting">⚠️ {msg}</div>',
                                unsafe_allow_html=True
                            )
                            status_text.text(msg)
                    
                    frame_counter += 1
                    
                    # Draw face oval guide
                    h, w = frame.shape[:2]
                    cv2.ellipse(frame, (w//2, h//2), (w//4, h//3), 0, 0, 360, (0,255,255), 2)
                    
                    FRAME_WINDOW.image(cv2.cvtColor(frame, cv2.COLOR_BGR2RGB), use_container_width=True)  # Fixed deprecation
                    time.sleep(0.033)  # ~30fps
                    
            finally:
                cap.release()
                cv2.destroyAllWindows()
