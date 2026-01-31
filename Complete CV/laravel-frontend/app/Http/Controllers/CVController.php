<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Models\CVSubmission;

class CVController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function showForm()
    {
        return view('form');
    }

    public function generate(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'dob' => 'nullable|date',
            'linkedin' => 'nullable|url',
            
            // Education
            'university' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'major_subject' => 'nullable|string|max:255',
            'cgpa' => 'required|string|max:10',
            'degree_status' => 'required|in:Completed,In Progress,Discontinued',
            'passout_session' => 'nullable|string|max:50',
            'final_year_project' => 'nullable|string|max:500',
            
            // Experience
            'organization' => 'nullable|string|max:255',
            'designation' => 'required|string|max:255',
            'experience_years' => 'required|string|max:10',
            'experience_status' => 'required|in:Current,Past',
            'industry' => 'required|string|max:255',
            'work_detail' => 'nullable|string|max:500',
            
            // Skills
            'technical_skills' => 'required|string|max:1000',
            'it_skills' => 'nullable|string|max:500',
            'interests' => 'nullable|string|max:500',
            'certifications' => 'nullable|string|max:500',
            'medal_holder' => 'nullable|string|max:255',
            
            // Achievements
            'achievements' => 'required|string|max:1000',
            'honours' => 'nullable|string|max:500',
            
            // References
            'ref_name' => 'nullable|string|max:255',
            'ref_designation' => 'nullable|string|max:255',
            'ref_organization' => 'nullable|string|max:255',
            'ref_contact' => 'nullable|string|max:20',
            'ref_email' => 'nullable|email',
            
            // Aspirations
            'aspiration' => 'required|in:Interested in studying abroad,Want to serve Family Business,New Start-up (Business),Work as a freelancer,Interested in Job,Want to continue studies in Pakistan',
            'num_versions' => 'required|integer|min:1|max:3',
        ]);

        try {
            // Send data to Python backend
            $response = Http::post('http://localhost:5000/api/generate-cv', [
                'data' => $validated
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Store in session
                $submissionId = uniqid();
                Session::put('cv_result_' . $submissionId, [
                    'input_data' => $validated,
                    'generated_sections' => $result['generated_sections'],
                    'timestamp' => now(),
                ]);

                return redirect()->route('result', ['id' => $submissionId]);
            } else {
                return back()->withErrors(['error' => 'Failed to generate CV. Please try again.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Connection to CV generator failed: ' . $e->getMessage()]);
        }
    }

    public function showResult($id)
    {
        $result = Session::get('cv_result_' . $id);
        
        if (!$result) {
            return redirect()->route('form')->withErrors(['error' => 'CV result not found.']);
        }

        return view('result', [
            'inputData' => $result['input_data'],
            'generatedSections' => $result['generated_sections'],
            'timestamp' => $result['timestamp'],
            'submissionId' => $id,
        ]);
    }
}