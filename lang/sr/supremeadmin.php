<?php

return [
    'page_title'         => 'Pregled za Supreme Admina',
    'main_heading'       => 'Pregled za Supreme Admina',
    'sub_heading'        => 'Pregled svih promoter menadžera, njihovih sub-promotera i koliko je ko zaradio ili duguje festivalu.',
    'search_placeholder' => 'Pretraga po imenu ili email-u…',
    'search_button'      => 'Pretraži',
    'clear_button'       => 'Obriši',
    'empty_results'      => 'Nema promoter menadžera koji odgovaraju trenutnim filterima.',

    'kpi' => [
        'managers'    => 'Menadžeri',
        'subs'        => 'Sub-promoteri',
        'tickets'     => 'Prodate karte',
        'gross_sales' => 'Bruto prodaja',
        'commission'  => 'Zarada (provizija)',
        'paid'        => 'Uplaćeno festivalu',
        'owed'        => 'Duguje festivalu',
    ],

    'fields' => [
        'gross_sales'       => 'Bruto prodaja',
        'commission_earned' => 'Zarada (provizija)',
        'paid'              => 'Uplaćeno festivalu',
        'owed'              => 'Duguje festivalu',
    ],

    'scope' => [
        'team' => 'Ceo tim',
        'self' => 'Samo ovaj korisnik',
    ],

    'filters' => [
        'heading'       => 'Filteri',
        'field_label'   => 'Polje',
        'op_label'      => 'Operator',
        'amount_label'  => 'Iznos',
        'scope_label'   => 'Primeni na',
        'add_button'    => 'Dodaj filter',
        'apply_button'  => 'Primeni',
        'reset_button'  => 'Resetuj',
        'empty_hint'    => 'Nema filtera. Dodaj filter da suziš listu.',
    ],

    'table' => [
        'header_manager'           => 'Promoter Menadžer',
        'header_subs'              => 'Sub-ova',
        'header_gross_sales'       => 'Bruto prodaja',
        'header_commission_earned' => 'Zarada',
        'header_paid'              => 'Uplaćeno festivalu',
        'header_owed'              => 'Duguje festivalu',
        'header_tickets'           => 'Karte',
    ],

    'sub' => [
        'empty'                => 'Ovaj menadžer nema sub-promotera.',
        'header_name'          => 'Sub-promoter',
        'header_orders'        => 'Narudžbine',
        'header_tickets'       => 'Karte',
        'header_gross_sales'   => 'Bruto prodaja',
        'header_commission'    => 'Provizija',
        'header_paid'          => 'Uplaćeno',
        'header_owed'          => 'Duguje',
        'manager_self_label'   => 'Direktno (menadžer)',
    ],
];