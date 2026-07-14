<?php

namespace App\DataTransferObjects\Contact;

use App\Models\ContactLead;

readonly class ContactLeadResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?ContactLead $lead = null,
    ) {}

    public static function success(string $message, ?ContactLead $lead = null): self
    {
        return new self(true, $message, $lead);
    }

    public static function failure(string $message, ?ContactLead $lead = null): self
    {
        return new self(false, $message, $lead);
    }
}
