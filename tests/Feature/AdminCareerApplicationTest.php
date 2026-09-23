<?php

namespace Tests\Feature;

use App\Models\CareerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCareerApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_frontend_career_applications(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $application = $this->makeCareerApplication();

        $this->actingAs($admin)
            ->get(route('admin.career-applications.index', ['area' => 'brand-content', 'status' => 'new']))
            ->assertOk()
            ->assertSee($application->reference_no)
            ->assertSee('Ananya Rao')
            ->assertSee('Brand &amp; Content', escape: false);

        $this->actingAs($admin)
            ->get(route('admin.career-applications.index', ['area' => 'technology']))
            ->assertOk()
            ->assertDontSee($application->reference_no);
    }

    public function test_admin_can_view_full_application_and_download_cv(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $application = $this->makeCareerApplication();
        Storage::disk('local')->put($application->cv_path, 'sample cv content');

        $this->actingAs($admin)
            ->get(route('admin.career-applications.show', $application))
            ->assertOk()
            ->assertSee('Ananya Rao')
            ->assertSee('Content Designer')
            ->assertSee('I would love to help shape thoughtful jewellery stories.');

        $this->actingAs($admin)
            ->get(route('admin.career-applications.cv', $application))
            ->assertOk()
            ->assertDownload('ananya-rao-cv.pdf');
    }

    public function test_admin_can_update_application_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $application = $this->makeCareerApplication();

        $this->actingAs($admin)
            ->patch(route('admin.career-applications.update-status', $application), ['status' => 'shortlisted'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('career_applications', [
            'id' => $application->id,
            'status' => 'shortlisted',
        ]);
    }

    public function test_non_admin_cannot_access_career_applications(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $application = $this->makeCareerApplication();

        $this->actingAs($customer)
            ->get(route('admin.career-applications.show', $application))
            ->assertForbidden();
    }

    private function makeCareerApplication(): CareerApplication
    {
        return CareerApplication::create([
            'reference_no' => 'RPN-CAR-2026-TEST0001',
            'full_name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'phone' => '+91 98765 43210',
            'location' => 'Bengaluru, Karnataka',
            'area_of_interest' => 'brand-content',
            'current_role' => 'Content Designer',
            'experience_years' => 4,
            'linkedin_url' => 'https://www.linkedin.com/in/ananya-rao',
            'portfolio_url' => 'https://ananya.example.com',
            'message' => 'I would love to help shape thoughtful jewellery stories.',
            'cv_path' => 'career-applications/2026/09/ananya-rao-cv.pdf',
            'cv_original_name' => 'ananya-rao-cv.pdf',
            'cv_mime_type' => 'application/pdf',
            'cv_size' => 262144,
            'status' => 'new',
        ]);
    }
}
