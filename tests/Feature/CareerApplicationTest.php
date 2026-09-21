<?php

namespace Tests\Feature;

use App\Models\CareerApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_careers_page_contains_the_application_modal(): void
    {
        $this->get(route('careers'))
            ->assertOk()
            ->assertSee('Introduce Yourself')
            ->assertSee(route('career-applications.store'), escape: false);
    }

    public function test_candidate_can_submit_an_application_with_a_cv(): void
    {
        Storage::fake('local');

        $response = $this->post(route('career-applications.store'), [
            'full_name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'phone' => '+91 98765 43210',
            'location' => 'Bengaluru, Karnataka',
            'area_of_interest' => 'brand-content',
            'current_role' => 'Content Designer',
            'experience_years' => 4,
            'linkedin_url' => 'https://www.linkedin.com/in/ananya-rao',
            'portfolio_url' => 'https://ananya.example.com',
            'message' => 'I would love to help shape thoughtful jewellery stories for Rupnora customers.',
            'cv' => UploadedFile::fake()->create('ananya-rao-cv.pdf', 256, 'application/pdf'),
        ]);

        $response
            ->assertRedirect(route('careers').'#openings')
            ->assertSessionHas('career_application_success');

        $application = CareerApplication::firstOrFail();

        $this->assertSame('Ananya Rao', $application->full_name);
        $this->assertSame('brand-content', $application->area_of_interest);
        $this->assertSame('ananya-rao-cv.pdf', $application->cv_original_name);
        $this->assertSame('new', $application->status);
        $this->assertStringStartsWith('RPN-CAR-', $application->reference_no);
        Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_application_rejects_unsupported_cv_files(): void
    {
        Storage::fake('local');

        $response = $this->from(route('careers'))->post(route('career-applications.store'), [
            'full_name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'phone' => '+91 98765 43210',
            'location' => 'Bengaluru, Karnataka',
            'area_of_interest' => 'brand-content',
            'message' => 'I would love to work with the Rupnora brand and content team.',
            'cv' => UploadedFile::fake()->create('malware.exe', 50, 'application/x-msdownload'),
        ]);

        $response
            ->assertRedirect(route('careers'))
            ->assertSessionHasErrors(['cv'], errorBag: 'careerApplication');

        $this->assertDatabaseCount('career_applications', 0);
    }
}
