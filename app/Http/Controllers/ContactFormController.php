<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Contact\ContactLeadData;
use App\Http\Requests\StoreContactLeadRequest;
use App\Services\Contact\ContactLeadSubmissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactFormController extends Controller
{
    public function __construct(
        private readonly ContactLeadSubmissionService $submissionService,
    ) {}

    public function create(Request $request): View
    {
        $embed = $request->boolean('embed');

        $view = $embed ? 'contact-form.embed' : 'contact-form.show';

        return view($view, [
            'embed' => $embed,
            'formAction' => route('forms.contact.store', $embed ? ['embed' => 1] : []),
        ]);
    }

    public function store(StoreContactLeadRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->submissionService->submit(new ContactLeadData(
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            phone: $validated['phone'],
            message: $validated['message'],
        ));

        $redirect = redirect()
            ->route('page.contact')
            ->with('contact_status_type', $result->success ? 'success' : 'error')
            ->with('contact_status_message', $result->message);

        if ($result->success) {
            return $redirect->withInput([
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'message' => '',
            ]);
        }

        return $redirect->withInput();
    }
}
