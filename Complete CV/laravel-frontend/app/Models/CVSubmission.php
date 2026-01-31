<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CVSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'dob',
        'linkedin',
        'university',
        'degree',
        'department',
        'major_subject',
        'cgpa',
        'degree_status',
        'passout_session',
        'final_year_project',
        'organization',
        'designation',
        'experience_years',
        'experience_status',
        'industry',
        'work_detail',
        'technical_skills',
        'it_skills',
        'interests',
        'certifications',
        'medal_holder',
        'achievements',
        'honours',
        'ref_name',
        'ref_designation',
        'ref_organization',
        'ref_contact',
        'ref_email',
        'aspiration',
        'generated_content',
    ];
}