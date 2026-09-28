<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInfluencerApplicationRequest;
use App\Models\Influencer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InfluencerController extends Controller
{
    public function index(): View
    {
        return view('pages.influencer', ['title' => 'Influencer Program']);
    }

    public function store(StoreInfluencerApplicationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('terms');
        $data['reference_no'] = Influencer::generateReferenceNumber();
        $data['user_id'] = $request->user()?->id;
        $data['status'] = 'new';
        $data['is_active'] = false;

        $influencer = Influencer::create($data);

        return redirect()
            ->to(route('influencer').'#apply')
            ->with(
                'influencer_application_success',
                'Thank you for applying. Your application reference is '.$influencer->reference_no.'.'
            );
    }
}
