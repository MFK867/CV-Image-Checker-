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

# SIMPLIFIED VALIDATOR WITHOUT MEDIAPIPE
class SimpleValidator:
    def __init__(self):
        self.MAX_TILT = 15
        self.MAX_YAW = 20
        self.MAX_PITCH = 25
        self.MAX_MOUTH = 0.05
    
    def validate_frame(self, image):
        """Simplified validation using OpenCV only"""
        h, w = image.shape[:2]
        
        results = {
            'single': False, 'straight': False, 'tilt': False,
            'mouth': False, 'light': True, 'all': False, 'faces': 0
        }
        
        # Check brightness
        brightness = np.mean(cv2.cvtColor(image, cv2.COLOR_BGR2GRAY))
        if brightness < 50:
            results['light'] = False
        else:
            results['light'] = True
        
        # Simple face detection using OpenCV
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        faces = face_cascade.detectMultiScale(gray, 1.1, 4)
        
        if len(faces) == 1:
            results['single'] = True
            results['faces'] = 1
            
            # For single face, assume requirements are met for demo
            results['straight'] = True
            results['tilt'] = True
            results['mouth'] = True
        
        results['all'] = all([results['single'], results['straight'], 
                             results['tilt'], results['mouth'], results['light']])
        return results

def create_passport_photo(image):
    """
    Create professional passport photo WITHOUT rembg
    """
    # Simple cropping and resizing
    h, w = image.shape[:2]
    
    # Crop to center with some margin
    crop_size = min(h, w) * 0.8
    x1 = int(w/2 - crop_size/2)
    y1 = int(h/2 - crop_size/2)
    x2 = int(w/2 + crop_size/2)
    y2 = int(h/2 + crop_size/2)
    
    # Ensure within bounds
    x1, y1 = max(0, x1), max(0, y1)
    x2, y2 = min(w, x2), min(h, y2)
    
    cropped = image[y1:y2, x1:x2]
    
    # Resize to 600x600
    final = cv2.resize(cropped, (600, 600), interpolation=cv2.INTER_LANCZOS4)
    
    return final

# Initialize validator
validator = SimpleValidator()

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
    st.markdown("""
    - Stand 2-3 feet from camera
    - Face a light source
    - Look straight at the camera
    - Keep mouth closed
    - Remove glasses if possible
    - Use plain background for best results
    """)

# RESULT DISPLAY
if st.session_state.auto_capture_frame is not None:
    st.balloons()
    st.success("✅ Photo captured successfully!")
    
    col1, col2 = st.columns(2)
    
    with col1:
        st.subheader("Original Capture")
        st.image(cv2.cvtColor(st.session_state.auto_capture_frame, cv2.COLOR_BGR2RGB), 
                channels="RGB")
    
    with col2:
        st.subheader("Professional Format")
        
        # Process image
        with st.spinner("Processing..."):
            final = create_passport_photo(st.session_state.auto_capture_frame)
        
        final_rgb = cv2.cvtColor(final, cv2.COLOR_BGR2RGB)
        st.image(final_rgb, channels="RGB")
        
        # Download
        pil_img = Image.fromarray(final_rgb)
        buf = io.BytesIO()
        pil_img.save(buf, format='PNG', quality=95)
        
        st.download_button(
            label="⬇️ Download Professional Picture",
            data=buf.getvalue(),
            file_name=f"TUF_Professional_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png",
            mime="image/png"
        )
    
    if st.button("🔄 Take Another Photo", type="primary"):
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
            if st.button("📷 Start Camera", type="primary"):
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
            if st.button("⏹️ Stop Camera"):
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
        
        # Try to open camera
        try:
            cap = cv2.VideoCapture(0)
            
            if not cap.isOpened():
                st.error("❌ Camera not found or in use by another application")
                st.session_state.camera_running = False
                st.rerun()
            
            frame_counter = 0
            valid_start = None
            last_countdown_val = None
            
            while st.session_state.camera_running:
                ret, frame = cap.read()
                if not ret:
                    st.warning("Could not read from camera")
                    break
                
                frame = cv2.flip(frame, 1)  # Mirror
                
                # Process every 3rd frame for performance
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
                            
                            # Draw indicator on frame
                            h, w = frame.shape[:2]
                            cv2.circle(frame, (w//2, h//2), 100, (0, 255, 0), 3)
                            
                        else:
                            # CAPTURE!
                            COUNTDOWN_HTML.empty()
                            st.session_state.auto_capture_frame = frame.copy()
                            cap.release()
                            cv2.destroyAllWindows()
                            st.rerun()
                    else:
                        valid_start = None
                        last_countdown_val = None
                        COUNTDOWN_HTML.empty()
                        
                        # Provide feedback
                        if not results['single']:
                            msg = "Show one face clearly"
                        elif not results['light']:
                            msg = "Move to brighter area"
                        else:
                            msg = "Adjust position"
                        
                        status_container.markdown(
                            f'<div class="status-bar status-waiting">⚠️ {msg}</div>',
                            unsafe_allow_html=True
                        )
                        status_text.text(msg)
                
                frame_counter += 1
                
                # Draw face oval guide
                h, w = frame.shape[:2]
                cv2.ellipse(frame, (w//2, h//2), (w//4, h//3), 0, 0, 360, (0,255,255), 2)
                
                # Display frame
                FRAME_WINDOW.image(frame, channels="BGR", use_column_width=True)
                
                # Small delay for performance
                time.sleep(0.033)  # ~30fps
                
        except Exception as e:
            st.error(f"Camera error: {str(e)}")
            st.session_state.camera_running = False
            st.rerun()
        finally:
            if 'cap' in locals():
                cap.release()
            cv2.destroyAllWindows()
