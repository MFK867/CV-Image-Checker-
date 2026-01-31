<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV About Me Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .section-header {
            background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 20px 0 10px 0;
            font-weight: 600;
        }
        .generated-box {
            background-color: #f8f9fa;
            padding: 24px;
            border-radius: 12px;
            border-left: 4px solid #4CAF50;
            margin: 16px 0;
            line-height: 1.6;
            font-size: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            text-align: center;
            flex: 1;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step.active .step-circle {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <i class="fas fa-file-alt text-green-500"></i> CV About Me Generator
            </h1>
            <p class="text-gray-600">Generate market-optimized professional CV summaries</p>
            <div class="w-24 h-1 bg-green-500 mx-auto mt-4"></div>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Progress Steps -->
        <div class="step-indicator mb-8">
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-title">Personal Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-title">Education</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-title">Experience</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-title">Skills & More</div>
            </div>
        </div>

        <!-- Form -->
        <form id="cvForm" action="{{ route('generate') }}" method="POST" class="bg-white rounded-xl shadow-lg p-6 mb-8">
            @csrf

            <!-- Step 1: Personal Information -->
            <div class="form-step active" id="step1">
                <div class="section-header">
                    <i class="fas fa-user mr-2"></i> Personal Information
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Faisal Kamran">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="example@email.com">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="mobile">Mobile Number *</label>
                        <input type="text" id="mobile" name="mobile" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="0312-XXXXXXX">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="address">Address</label>
                        <input type="text" id="address" name="address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Your complete address">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="linkedin">LinkedIn Profile</label>
                        <input type="url" id="linkedin" name="linkedin"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="https://linkedin.com/in/yourprofile">
                    </div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <div></div>
                    <button type="button" onclick="nextStep(2)" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Education -->
            <div class="form-step" id="step2">
                <div class="section-header">
                    <i class="fas fa-graduation-cap mr-2"></i> Education
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="university">University *</label>
                        <input type="text" id="university" name="university" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="The University of Faisalabad">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="degree">Degree Name *</label>
                        <input type="text" id="degree" name="degree" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Bachelor of Science in Computer Science">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="department">Department *</label>
                        <input type="text" id="department" name="department" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Computer Science">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="major_subject">Major Subject</label>
                        <input type="text" id="major_subject" name="major_subject"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Software Engineering">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="cgpa">CGPA / Percentage *</label>
                        <input type="text" id="cgpa" name="cgpa" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="3.5/4.0 or 85%">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="degree_status">Degree Status *</label>
                        <select id="degree_status" name="degree_status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Completed">Completed</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Discontinued">Discontinued</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="passout_session">Pass out Session</label>
                        <input type="text" id="passout_session" name="passout_session"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="2023-2024">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-gray-700 mb-2" for="final_year_project">Final Year Project/Research Work</label>
                    <textarea id="final_year_project" name="final_year_project" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Describe your final year project..." maxlength="250"></textarea>
                    <div class="text-right text-sm text-gray-500 mt-1" id="project-counter">0/250</div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep(1)" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>
                    <button type="button" onclick="nextStep(3)" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Experience -->
            <div class="form-step" id="step3">
                <div class="section-header">
                    <i class="fas fa-briefcase mr-2"></i> Experience
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="organization">Organization</label>
                        <input type="text" id="organization" name="organization"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Nextech">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="designation">Designation/Role *</label>
                        <input type="text" id="designation" name="designation" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Web Developer">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="experience_years">Years of Experience *</label>
                        <input type="text" id="experience_years" name="experience_years" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="2 years">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="experience_status">Experience Status *</label>
                        <select id="experience_status" name="experience_status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Current">Current</option>
                            <option value="Past">Past</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="industry">Industry *</label>
                        <input type="text" id="industry" name="industry" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Information Technology">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-gray-700 mb-2" for="work_detail">Work Detail</label>
                    <textarea id="work_detail" name="work_detail" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Describe your work responsibilities..." maxlength="250"></textarea>
                    <div class="text-right text-sm text-gray-500 mt-1" id="work-counter">0/250</div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep(2)" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>
                    <button type="button" onclick="nextStep(4)" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 4: Skills, Achievements & More -->
            <div class="form-step" id="step4">
                <div class="section-header">
                    <i class="fas fa-cogs mr-2"></i> Skills & Interests
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="technical_skills">Technical Skills *</label>
                        <textarea id="technical_skills" name="technical_skills" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="PHP, Laravel, JavaScript, MySQL..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="it_skills">IT Skills</label>
                        <textarea id="it_skills" name="it_skills" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="MS Office, Photoshop, etc."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="interests">Personal Interests</label>
                        <textarea id="interests" name="interests" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Reading, Traveling, Sports..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="certifications">Certifications</label>
                        <input type="text" id="certifications" name="certifications"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="AWS Certified, PMP, etc.">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="medal_holder">Medals/Awards</label>
                        <input type="text" id="medal_holder" name="medal_holder"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Gold Medal in University">
                    </div>
                </div>
                
                <div class="section-header mt-8">
                    <i class="fas fa-trophy mr-2"></i> Achievements & Honours
                </div>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="achievements">Key Achievements *</label>
                        <textarea id="achievements" name="achievements" rows="4" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Describe your key achievements..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="honours">Honours & Awards (Optional)</label>
                        <textarea id="honours" name="honours" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Dean's List, Scholarships, etc."></textarea>
                    </div>
                </div>
                
                <div class="section-header mt-8">
                    <i class="fas fa-users mr-2"></i> References
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="ref_name">Reference Name</label>
                        <input type="text" id="ref_name" name="ref_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="ref_designation">Reference Designation</label>
                        <input type="text" id="ref_designation" name="ref_designation"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="ref_organization">Reference Organization</label>
                        <input type="text" id="ref_organization" name="ref_organization"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="ref_contact">Reference Contact</label>
                        <input type="text" id="ref_contact" name="ref_contact"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="ref_email">Reference Email</label>
                        <input type="email" id="ref_email" name="ref_email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="section-header mt-8">
                    <i class="fas fa-bullseye mr-2"></i> Career Aspirations
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="aspiration">Future Aspirations *</label>
                        <select id="aspiration" name="aspiration" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Interested in studying abroad">Interested in studying abroad</option>
                            <option value="Want to serve Family Business">Want to serve Family Business</option>
                            <option value="New Start-up (Business)">New Start-up (Business)</option>
                            <option value="Work as a freelancer">Work as a freelancer</option>
                            <option value="Interested in Job">Interested in Job</option>
                            <option value="Want to continue studies in Pakistan">Want to continue studies in Pakistan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2" for="num_versions">Versions to Generate *</label>
                        <input type="number" id="num_versions" name="num_versions" min="1" max="3" value="1" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <button type="button" onclick="prevStep(3)" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>
                    <button type="submit" class="bg-green-500 text-white px-8 py-3 rounded-lg hover:bg-green-600 transition font-bold">
                        <i class="fas fa-magic mr-2"></i> Generate CV Summary
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-8 rounded-xl text-center max-w-md">
                <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-green-500 mx-auto mb-4"></div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Generating About section</h3>
                <p class="text-gray-600">This may take 30-60 seconds. Please wait...</p>
                <div class="mt-4 text-sm text-gray-500">
                    <i class="fas fa-lightbulb mr-1"></i> Using AI to create optimized content
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        
        function nextStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            currentStep = step;
            document.getElementById(`step${currentStep}`).classList.add('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
        }
        
        function prevStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            currentStep = step;
            document.getElementById(`step${currentStep}`).classList.add('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
        }
        
        // Character counters
        document.getElementById('final_year_project').addEventListener('input', function() {
            document.getElementById('project-counter').textContent = this.value.length + '/250';
        });
        
        document.getElementById('work_detail').addEventListener('input', function() {
            document.getElementById('work-counter').textContent = this.value.length + '/250';
        });
        
        // Form submission
        document.getElementById('cvForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        });
        
        // Initialize character counters
        document.getElementById('project-counter').textContent = 
            document.getElementById('final_year_project').value.length + '/250';
        document.getElementById('work-counter').textContent = 
            document.getElementById('work_detail').value.length + '/250';
    </script>
</body>
</html>