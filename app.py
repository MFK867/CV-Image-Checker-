import streamlit as st
import cv2
import numpy as np
from PIL import Image
import mediapipe as mp
from rembg import remove
import io

# Page configuration - MUST be first Streamlit command
st.set_page_config(
    page_title="CV Photo Validator",
    page_icon="📸",
    layout="wide"
)

# Initialize MediaPipe Face Detection and Face Mesh
mp_face_detection = mp.solutions.face_detection
mp_face_mesh = mp.solutions.face_mesh

def check_eyes_open(face_landmarks):
    """Check if eyes are open using eye aspect ratio"""
    left_eye = [face_landmarks.landmark[i] for i in [159, 145, 133, 33]]
    right_eye = [face_landmarks.landmark[i] for i in [386, 374, 362, 263]]
    
    def eye_aspect_ratio(eye):
        v1 = abs(eye[1].y - eye[3].y)
        v2 = abs(eye[0].y - eye[2].y)
        h = abs(eye[0].x - eye[2].x)
        ear = (v1 + v2) / (2.0 * h)
        return ear
    
    left_ear = eye_aspect_ratio(left_eye)
    right_ear = eye_aspect_ratio(right_eye)
    avg_ear = (left_ear + right_ear) / 2.0
    
    return avg_ear > 0.15, avg_ear

def check_mouth_closed(face_landmarks):
    """Check if mouth is closed"""
    upper_lip = face_landmarks.landmark[13]
    lower_lip = face_landmarks.landmark[14]
    mouth_opening = abs(upper_lip.y - lower_lip.y)
    return mouth_opening < 0.02, mouth_opening

def check_face_alignment(face_landmarks, image_width, image_height):
    """Check if face is straight and centered"""
    nose_tip = face_landmarks.landmark[1]
    left_eye = face_landmarks.landmark[33]
    right_eye = face_landmarks.landmark[263]
    
    eye_diff_y = abs(left_eye.y - right_eye.y)
    is_straight = eye_diff_y < 0.03
    
    nose_x = nose_tip.x
    is_centered = 0.35 < nose_x < 0.65
    
    return is_straight and is_centered, is_straight, is_centered

def validate_cv_photo(image):
    """Validate if photo meets CV requirements"""
    img_array = np.array(image)
    img_rgb = cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
    
    issues = []
    details = {}
    
    # Face Detection
    face_detection = mp_face_detection.FaceDetection(min_detection_confidence=0.5)
    results = face_detection.process(cv2.cvtColor(img_rgb, cv2.COLOR_BGR2RGB))
    face_detection.close()
    
    if not results.detections:
        issues.append("❌ No face detected in the image")
        return False, issues, None, details
    
    if len(results.detections) > 1:
        issues.append(f"❌ Multiple faces detected ({len(results.detections)}). Only one person should be in the photo")
        return False, issues, None, details
    
    # Face Mesh for detailed analysis
    face_mesh = mp_face_mesh.FaceMesh(
        static_image_mode=True,
        max_num_faces=1,
        min_detection_confidence=0.5
    )
    results = face_mesh.process(cv2.cvtColor(img_rgb, cv2.COLOR_BGR2RGB))
    face_mesh.close()
    
    if results.multi_face_landmarks:
        face_landmarks = results.multi_face_landmarks[0]
        
        eyes_open, ear_value = check_eyes_open(face_landmarks)
        details['eye_aspect_ratio'] = round(ear_value, 3)
        if not eyes_open:
            issues.append("❌ Eyes appear to be closed or partially closed")
        
        mouth_closed, mouth_value = check_mouth_closed(face_landmarks)
        details['mouth_opening'] = round(mouth_value, 3)
        if not mouth_closed:
            issues.append("❌ Mouth appears to be open")
        
        is_aligned, is_straight, is_centered = check_face_alignment(
            face_landmarks, img_rgb.shape[1], img_rgb.shape[0]
        )
        details['face_straight'] = is_straight
        details['face_centered'] = is_centered
        
        if not is_straight:
            issues.append("❌ Face is tilted - please keep your head straight")
        if not is_centered:
            issues.append("❌ Face is not centered in the frame")
    
    is_valid = len(issues) == 0
    return is_valid, issues, img_array, details

def remove_background(image):
    """Remove background and replace with white"""
    img_byte_arr = io.BytesIO()
    image.save(img_byte_arr, format='PNG')
    img_byte_arr = img_byte_arr.getvalue()
    
    output = remove(img_byte_arr)
    img_no_bg = Image.open(io.BytesIO(output)).convert("RGBA")
    
    white_bg = Image.new("RGBA", img_no_bg.size, "WHITE")
    white_bg.paste(img_no_bg, (0, 0), img_no_bg)
    
    return white_bg.convert("RGB")

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
            is_valid, issues, img_array, details = validate_cv_photo(image)
        
        with col2:
            st.subheader("Validation Results")
            
            if is_valid:
                st.success("✅ **Photo is suitable for CV!**")
                st.balloons()
                
                with st.expander("Technical Details"):
                    st.json(details)
                
                if st.button("🎨 Remove Background & Make White", key="remove_bg_upload"):
                    with st.spinner("Processing..."):
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
    2. Position yourself in front of the camera
    3. Take the photo when ready
    4. The photo will be validated automatically
    """)
    
    # Camera control
    col_btn1, col_btn2, col_btn3 = st.columns([1, 1, 2])
    
    with col_btn1:
        if st.button("📷 Open Camera", type="primary", disabled=st.session_state.camera_active):
            st.session_state.camera_active = True
    
    with col_btn2:
        if st.button("❌ Close Camera", type="secondary", disabled=not st.session_state.camera_active):
            st.session_state.camera_active = False
    
    # Camera input
    camera_photo = None
    if st.session_state.camera_active:
        st.info("📸 Camera is active. Take a photo when ready!")
        camera_photo = st.camera_input("", key="camera", label_visibility="collapsed")
    
    # Process captured photo
    if camera_photo is not None:
        image = Image.open(camera_photo)
        
        with st.spinner("Analyzing photo..."):
            is_valid, issues, img_array, details = validate_cv_photo(image)
        
        # Show validation results
        if is_valid:
            st.success("🎉 Your photo looks great!")
        else:
            st.warning("⚠️ Photo needs adjustment:")
            for issue in issues:
                st.markdown(f"- {issue}")
        
        # Display images
        col1, col2 = st.columns(2)
        
        with col1:
            st.subheader("Captured Photo")
            st.image(image, use_container_width=True)
        
        with col2:
            if is_valid:
                st.subheader("With White Background")
                with st.spinner("Removing background..."):
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

# Footer
st.markdown("---")
st.markdown("""
<div style='text-align: center'>
    <p>💡 <strong>Tips for a perfect CV photo:</strong></p>
    <p>✓ Wear professional attire | ✓ Good lighting | ✓ Neutral expression | ✓ Clean background</p>
</div>
""", unsafe_allow_html=True)
