<?php

namespace App\DataTransferObjects\Contact;

readonly class ContactLeadData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $message,
    ) {}

    public function fullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }
}
