# CV Photo Validator 📸

A professional CV photo validation and enhancement tool built with Python and Streamlit. This application helps users ensure their CV photos meet professional standards and automatically removes backgrounds.

## Features

### 1. Upload Photo Mode 📤
- Upload existing photos (JPG, JPEG, PNG)
- Automated validation checks:
  - ✅ Single person detection
  - ✅ Eyes open verification
  - ✅ Mouth closed verification
  - ✅ Face alignment and centering
  - ✅ Straight face orientation
- Background removal with white background replacement
- Downloadable final image

### 2. Camera Capture Mode 📷
- Live camera feed with real-time feedback
- Step-by-step guidance:
  - Face detection status
  - Eyes open/closed indicator
  - Mouth open/closed indicator
  - Face alignment guidance
  - Centering assistance
- Automatic capture readiness detection
- Instant background removal to white
- Downloadable final image

## Technology Stack

- **Streamlit**: Web application framework
- **OpenCV**: Image processing
- **MediaPipe**: Face detection and landmark analysis
- **rembg**: AI-powered background removal
- **PIL/Pillow**: Image manipulation

## Installation

### Local Development

1. Clone the repository or download the files

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Run the application:
```bash
streamlit run app.py
```

4. Open your browser at `http://localhost:8501`

## Deployment on Streamlit Cloud

### Quick Deployment Steps:

1. **Create a GitHub Repository**
   - Create a new repository on GitHub
   - Upload these files:
     - `app.py`
     - `requirements.txt`
     - `packages.txt`
     - `.streamlit/config.toml`

2. **Deploy to Streamlit Cloud**
   - Go to [share.streamlit.io](https://share.streamlit.io)
   - Sign in with GitHub
   - Click "New app"
   - Select your repository
   - Set main file path: `app.py`
   - Click "Deploy"

3. **Wait for Deployment**
   - Initial deployment takes 5-10 minutes
   - The app will automatically install all dependencies
   - Once complete, you'll get a public URL

### Important Notes for Deployment:

- The `packages.txt` file contains system-level dependencies required for OpenCV
- The `requirements.txt` uses `opencv-python-headless` which is optimized for cloud deployment
- The `.streamlit/config.toml` file optimizes the app for cloud hosting

## Usage Guide

### For Upload Mode:
1. Click the "📤 Upload Photo" tab
2. Upload your photo
3. Review the validation results
4. If valid, click "Remove Background & Make White"
5. Download your professional CV photo

### For Camera Mode:
1. Click the "📷 Take Photo" tab
2. Allow camera permissions
3. Follow the live feedback:
   - Position yourself in frame
   - Keep eyes open
   - Close your mouth
   - Keep head straight
   - Center your face
4. When all checks show ✅, your photo is automatically processed
5. Download your professional CV photo with white background

## Validation Criteria

The app checks for:

1. **Face Detection**: Exactly one face must be detected
2. **Eyes**: Must be open (Eye Aspect Ratio > 0.15)
3. **Mouth**: Must be closed (vertical opening < 0.02)
4. **Alignment**: Head should be straight (eye level difference < 0.03)
5. **Centering**: Face should be centered in frame (0.35 < x < 0.65)

## File Structure

```
cv-photo-validator/
├── app.py                      # Main Streamlit application
├── requirements.txt            # Python dependencies
├── packages.txt               # System dependencies for deployment
├── .streamlit/
│   └── config.toml           # Streamlit configuration
└── README.md                  # This file
```

## Troubleshooting

### Local Issues:

**Camera not working?**
- Ensure browser has camera permissions
- Try using a different browser (Chrome recommended)

**Background removal slow?**
- First run downloads the AI model (~170MB)
- Subsequent runs will be faster

### Deployment Issues:

**App won't start?**
- Check that all files are in the repository
- Verify `packages.txt` is present (required for OpenCV)
- Check Streamlit Cloud logs for specific errors

**Camera not working on mobile?**
- Ensure HTTPS is enabled (Streamlit Cloud does this automatically)
- Check mobile browser permissions

## Performance Optimization

- Uses `opencv-python-headless` for smaller deployment size
- Efficient face detection with MediaPipe
- Background removal cached for faster repeat processing

## Browser Compatibility

- ✅ Chrome (Recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- 📱 Mobile browsers (iOS Safari, Chrome Mobile)

## Privacy & Security

- All processing happens client-side or on the Streamlit server
- No images are stored permanently
- No data is shared with third parties
- Images are processed in memory only

## Tips for Best Results

1. **Lighting**: Use good, even lighting
2. **Background**: Any background works (will be removed)
3. **Attire**: Wear professional clothing
4. **Expression**: Neutral, professional expression
5. **Distance**: Position yourself at arm's length from camera

## License

This project is provided as-is for educational and professional use.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review Streamlit documentation: https://docs.streamlit.io
3. Check MediaPipe documentation: https://google.github.io/mediapipe/

## Updates & Maintenance

Regular updates include:
- Dependency updates for security
- Performance improvements
- Additional validation features
- UI/UX enhancements

---

**Made with ❤️ using Streamlit**
