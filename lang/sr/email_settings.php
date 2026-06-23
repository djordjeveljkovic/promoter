<?php

return [
    'page_title'  => 'Podešavanja emaila',
    'main_heading'=> 'Podešavanja emaila',

    // Tabovi na glavnoj strani
    'tabs' => [
        'config'    => 'Konfiguracija slanja',
        'templates' => 'Šabloni',
    ],

    // ===========================================================
    // Tab 1: Konfiguracija slanja
    // ===========================================================
    'config' => [
        'section_sender' => [
            'heading' => 'Ko šalje email',
            'help'    => 'Prikazuje se primaocu kao pošiljalac svakog email-a.',
        ],
        'section_server' => [
            'heading' => 'Server (SMTP)',
            'help'    => 'Podaci za povezivanje na mail server. Ostavite polje prazno da koristite vrednost iz .env.',
        ],
        'section_test' => [
            'heading' => 'Testiranje konfiguracije',
            'help'    => 'Šalje jedan običan tekstualni email da proverite da li konfiguracija radi.',
        ],

        'from_name_label'         => 'Ime pošiljaoca',
        'from_name_placeholder'   => 'npr. REFEST Festival',
        'from_address_label'      => 'Email pošiljaoca (From adresa)',
        'from_address_placeholder'=> 'tickets@refest.rs',

        'mailer_label'         => 'Mail driver',
        'mailer_help'          => 'smtp za pravi email, log za upis u laravel.log, array za testove.',
        'host_label'           => 'SMTP Host',
        'host_placeholder'     => 'mail.refest.rs',
        'port_label'           => 'SMTP Port',
        'port_placeholder'     => '465',
        'username_label'       => 'SMTP korisničko ime',
        'username_placeholder' => 'prodaja@refest.rs',
        'password_label'       => 'SMTP lozinka (ostavite prazno da zadržite trenutnu)',
        'password_placeholder' => '•••••••• (nepromenjeno)',
        'clear_password_label' => 'Obriši sačuvanu lozinku (vrati na .env)',
        'encryption_label'     => 'Šifrovanje',
        'encryption_none'      => 'Bez',
        'timeout_label'        => 'SMTP Timeout (sekunde)',
        'timeout_placeholder'  => '30',

        'test_recipient_label' => 'Podrazumevani test primalac',
        'test_recipient_help'  => 'Gde se šalje test email kada kliknete dugme ispod.',
        'test_subject_label'   => 'Naslov',
        'test_message_label'   => 'Telo poruke',

        'submit_button'           => 'Sačuvaj konfiguraciju',
        'send_test_button'        => 'Pošalji test email',

        'currently_effective' => 'Trenutno aktivno',
    ],

    // ===========================================================
    // Tab 2: Šabloni (lista)
    // ===========================================================
    'templates_list' => [
        'heading'        => 'Email šabloni',
        'help_text'      => 'Svaki šablon je Blade prikaz (preporučeno) ili inline HTML telo. Šablon označen kao Podrazumevani se koristi za slanje email-ova sa ulaznicama.',

        'add_button'     => 'Dodaj šablon',

        'header_name'    => 'Naziv',
        'header_subject' => 'Naslov',
        'header_source'  => 'Izvor',
        'header_default' => 'Podrazumevani',
        'header_actions' => 'Akcije',

        'source_view'    => 'Blade prikaz',
        'source_html'    => 'Inline HTML',

        'default_badge'    => 'Podrazumevani',

        'edit_button'      => 'Izmeni',
        'duplicate_button' => 'Dupliraj',
        'delete_button'    => 'Obriši',
        'make_default_button' => 'Postavi kao podrazumevani',
        'delete_confirm'   => 'Obriši šablon „:name"?',

        'empty' => 'Još nema šablona. Kliknite „Dodaj šablon" da kreirate jedan.',
    ],

    // ===========================================================
    // Stranica za dodavanje šablona
    // ===========================================================
    'create' => [
        'page_title'         => 'Dodaj email šablon',
        'back_to_list'       => 'Nazad na podešavanja emaila',
        'heading'            => 'Dodaj email šablon',
        'help_text'          => 'Šabloni koriste Blade tako da možete ubaciti dinamičke podatke sa {{ $order->email }}, @foreach petljama itd. Nakon čuvanja možete menjati kod sa pregledom uživo.',

        'name_label'        => 'Naziv šablona',
        'name_placeholder'  => 'npr. Ulaznice V2',
        'subject_label'     => 'Naslov emaila',
        'subject_placeholder' => 'npr. Vaše ulaznice za REFEST 2025',
        'description_label' => 'Opis (opciono)',
        'description_placeholder' => 'Interna beleška o šablonu',

        'source_type_label' => 'Tip izvora',
        'source_type_view'  => 'Blade prikaz (preporučeno — podržava @if, @foreach, {{ $order->email }} itd.)',
        'source_type_html'  => 'Inline HTML (jednostavno — podržava {{ $orderNumber }} placeholder)',
        'view_name_label'   => 'Putanja Blade prikaza',
        'view_name_placeholder' => 'emails.customer.tickets',
        'html_content_label'=> 'HTML telo',
        'html_content_placeholder' => '<h1>Zdravo!</h1><p>Vaša narudžbina {{ $orderNumber }} …</p>',

        'make_default_label' => 'Postavi kao podrazumevani šablon odmah nakon čuvanja',
        'submit_button'      => 'Kreiraj šablon',
        'cancel_button'      => 'Otkaži',
    ],

    // ===========================================================
    // Stranica za izmenu šablona (split view)
    // ===========================================================
    'edit' => [
        'page_title'       => 'Izmena email šablona',
        'back_to_list'     => 'Nazad na podešavanja emaila',

        'name_label'       => 'Naziv',
        'subject_label'    => 'Naslov emaila',
        'description_label'=> 'Opis',

        'make_default_label' => 'Koristi kao podrazumevani šablon',
        'make_default_help'  => 'Kada je čekirano, ovaj šablon se koristi za slanje email-ova sa ulaznicama. Samo jedan šablon može biti podrazumevani.',

        'editor_heading'   => 'Izvorni kod',
        'preview_heading'  => 'Pregled uživo',
        'preview_help'     => 'Renderovano sa probnim podacima — broj narudžbine, ime kupca, tipovi ulaznica i slike ovde su placeholder-i da vidite kako email izgleda pre slanja.',
        'preview_refresh_button' => 'Osveži pregled',
        'preview_iframe_title'   => 'Pregled emaila',

        'save_metadata_button'   => 'Sačuvaj podešavanja',
        'save_source_button'     => 'Sačuvaj kod',

        'source_size'         => ':size KB',
        'source_missing'      => 'Fajl nedostaje: :path',

        'editor_blade_variables' => 'Blade varijable: <code>$order</code> (TicketOrder), <code>$currencySymbol</code>, <code>$template</code>.',

        'danger_heading'   => 'Opasna zona',
        'danger_help'      => 'Brisanje šablona briše i njegov generisani Blade fajl. Ovo se ne može opozvati.',
        'delete_button'    => 'Obriši šablon',
        'delete_confirm'   => 'Obriši šablon „:name"?',
    ],

    // ===========================================================
    // Test email
    // ===========================================================
    'test_email' => [
        'default_subject' => 'Test email sa :app_name',
        'default_body'    => "Zdravo!\n\nOvo je test email poslat sa :app_name da se proveri konfiguracija emaila.\n\nMailer: :mailer\nHost: :host\nPort: :port\n\nAko ste dobili ovaj email, konfiguracija radi.",
    ],

    'duplicate_suffix' => '(duplikat)',
];
