<?php // resources/lang/sr/alert.php

return [
    // Poruke za Tipove Ulaznica
    'ticket_type_created_success' => 'Tip ulaznice je uspešno kreiran!',
    'ticket_type_create_failed'   => 'Kreiranje tipa ulaznice nije uspelo. Molimo pokušajte ponovo. Greška: :error',

    'ticket_type_updated_success' => 'Tip ulaznice je uspešno ažuriran!',
    'ticket_type_update_failed'   => 'Ažuriranje tipa ulaznice nije uspelo. Molimo pokušajte ponovo. Greška: :error',

    'ticket_type_deleted_success' => 'Tip ulaznice je uspešno obrisan!',
    'ticket_type_delete_failed'   => 'Brisanje tipa ulaznice nije uspelo. Greška: :error',

    'failed_to_create_directory' => 'Kreiranje direktorijuma nije uspelo: :error',
    'failed_to_move_photo'       => 'Premeštanje otpremljene fotografije nije uspelo: :error',
    'update_failed_create_directory' => 'Ažuriranje nije uspelo: Kreiranje direktorijuma nije moguće: :error',
    'update_failed_move_photo'       => 'Ažuriranje nije uspelo: Premeštanje nove fotografije nije moguće: :error',

    'order_created_success' => 'Narudžbina je uspešno kreirana! Procesiranje je pokrenuto za narudžbinu.',
    'order_created_failure' => 'Kreiranje narudžbine nije uspelo usled interne greške: :message',
    'image_generation_requeued' => 'Generisanje slike za narudžbinu je ponovo stavljeno u red.',
    'image_generation_cannot_rerun' => 'Generisanje slike za narudžbinu ne može biti ponovo pokrenuto iz trenutnog stanja (:status).',

    'email_requeued_success' => 'Email za narudžbinu je ponovo stavljen u red za slanje.',
    'email_cannot_resent' => 'Email za narudžbinu ne može biti ponovo poslat iz trenutnog stanja (:status).',

    'payment_amount_updated' => 'Iznos uplate je ažuriran.',
    'ticket_codes_not_found' => 'Nijedan od izabranih kodova ulaznica nije pronađen za ovu narudžbinu.',
    'no_tickets_to_process' => 'Nema dostupnih ulaznica za obradu za ovu narudžbinu.',
    'no_qr_codes_found' => 'Nisu pronađene slike QR kodova za navedene ulaznice.',
    'zip_creation_failed' => 'Kreiranje ZIP datoteke nije uspelo. Proverite dozvole servera ili logove.',

    'promoter_updated_success' => 'Promoter je uspešno ažuriran!',
    'auth_required' => 'Potrebna je autentifikacija.',

    // Poruke za Promoter Menadžere
    'promoter_manager_created_success' => 'Promoter menadžer je uspešno kreiran!',
    'promoter_manager_updated_success' => 'Promoter menadžer je uspešno ažuriran!',
    'promoter_manager_deleted_success' => 'Promoter menadžer je uspešno obrisan!',
    'sub_promoter_created_success'     => 'Sub-promoter je uspešno kreiran!',
    'sub_promoter_updated_success'     => 'Sub-promoter je uspešno ažuriran!',
    'sub_promoter_deleted_success'     => 'Sub-promoter je uspešno obrisan!',
    'ticket_type_created_success' => 'Tip ulaznice je uspešno kreiran!',
    'ticket_type_create_failed' => 'Kreiranje tipa ulaznice nije uspelo. Molimo pokušajte ponovo. Greška: :message',
    'ticket_type_updated_success' => 'Tip ulaznice je uspešno ažuriran!',
    'ticket_type_update_failed' => 'Ažuriranje tipa ulaznice nije uspelo. Greška: :message',
    'ticket_type_deleted_success' => 'Tip ulaznice je uspešno obrisan!',
    'ticket_type_delete_failed' => 'Brisanje tipa ulaznice nije uspelo. Greška: :message',

    'password_update_success' => 'Lozinka je uspešno ažurirana!',
    'password_update_failed' => 'Ažuriranje lozinke nije uspelo. Molimo pokušajte ponovo.',
    'validation_failed_check_fields' => 'Validacija nije uspela. Molimo proverite polja za unos radi grešaka.',

    // Poruke za evidentiranje uplata
    'payment_recorded_success'              => 'Uplata od :amount RSD od :name je evidentirana.',
    'payment_to_organizers_recorded_success'=> 'Uplata od :amount RSD organizatorima je evidentirana.',
    'payment_amount_invalid'                => 'Iznos uplate mora biti veći od nule.',

    // Poruke za email šablone
    'email_template_created'           => 'Email šablon „:name" je uspešno kreiran.',
    'email_template_updated'           => 'Email šablon „:name" je uspešno ažuriran.',
    'email_template_activated'         => 'Email šablon „:name" je sada aktivan. Svi budući email-ovi sa ulaznicama koristiće ovaj šablon.',
    'email_template_deleted'           => 'Email šablon „:name" je obrisan.',
    'email_template_name_required'     => 'Naziv šablona je obavezan.',
    'email_template_name_unique'       => 'Šablon sa ovim nazivom već postoji.',
    'email_template_subject_required'  => 'Naslov emaila je obavezan.',
    'email_template_view_string'       => 'Putanja Blade prikaza mora biti tekst.',
    'email_template_view_not_found'    => 'Blade prikaz „:view" ne postoji. Prvo kreirajte fajl prikaza ili ostavite polje prazno.',
    'email_template_view_required'     => 'Blade putanja je obavezna kada je tip izvora „Blade prikaz".',
    'email_template_html_required'     => 'HTML telo je obavezno kada je tip izvora „Inline HTML".',
    'email_template_source_type_required' => 'Izaberite da li šablon koristi Blade prikaz ili inline HTML.',
    'email_template_source_type_invalid'  => 'Tip izvora mora biti „view" ili „html".',
    'email_template_needs_view_or_html' => 'Morate uneti ili putanju Blade prikaza ili inline HTML telo za šablon.',
    'email_template_source_saved'      => 'Izvorni kod šablona „:name" je sačuvan.',
    'email_template_duplicated'        => 'Šablon „:name" je kreiran kao kopija. Možete ga sada nezavisno menjati.',
    'email_template_imported'          => 'Podrazumevani email je uvezen kao šablon „:name". Možete ga sada menjati.',

    // Podešavanja emaila (admin → /admin/email-settings)
    'email_settings_saved'                  => 'Konfiguracija emaila je sačuvana. Nove vrednosti su sada aktivne.',
    'email_settings_mailer_invalid'         => 'Mailer mora biti jedan od: smtp, sendmail, log, array, failover, roundrobin.',
    'email_settings_port_invalid'           => 'Port mora biti broj između 1 i 65535.',
    'email_settings_encryption_invalid'     => 'Šifrovanje mora biti „tls", „ssl" ili ostaviti prazno.',
    'email_settings_from_address_invalid'   => 'From adresa mora biti validna email adresa.',
    'email_settings_test_recipient_invalid' => 'Primalac mora biti validna email adresa.',
    'email_settings_test_recipient_required'=> 'Nema podešenog primaoca. Podesite „Test primaoca" u podešavanjima emaila ili prosledite „to" adresu.',
    'email_settings_test_sent'              => 'Test email je poslat na :to.',
    'email_settings_test_failed'            => 'Test email nije uspeo: :error',

    // Poruke za aktivaciju / deaktivaciju tipova ulaznica (zamenjuje brisanje).
    'ticket_type_deactivated_success'       => 'Tip ulaznice „:name" je deaktiviran. Postojeće ulaznice i narudžbine su zadržane.',
    'ticket_type_activated_success'         => 'Tip ulaznice „:name" je ponovo aktivan.',
    'ticket_type_toggle_failed'             => 'Promena statusa tipa ulaznice nije uspela. Greška: :message',
];
