<?php

return [
    'page_title'         => 'Pregled za Supreme Admina',
    'main_heading'       => 'Pregled za Supreme Admina',
    'sub_heading'        => 'Pregled svih promoter menadžera, njihovih promotera i koliko je ko zaradio ili duguje festivalu.',
    'search_placeholder' => 'Pretraga po imenu ili email-u…',
    'search_button'      => 'Pretraži',
    'clear_button'       => 'Obriši',
    'empty_results'      => 'Nema promoter menadžera koji odgovaraju trenutnim filterima.',

    'kpi' => [
        'managers'    => 'Menadžeri',
        'subs'        => 'Promoteri',
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
        'empty'                => 'Ovaj menadžer nema promotera.',
        'header_name'          => 'Promoter',
        'header_orders'        => 'Narudžbine',
        'header_tickets'       => 'Karte',
        'header_gross_sales'   => 'Bruto prodaja',
        'header_commission'    => 'Provizija',
        'header_paid'          => 'Uplaćeno',
        'header_owed'          => 'Duguje',
        'manager_self_label'   => 'Direktno (menadžer)',
    ],

    'users' => [
        'page_title'           => 'Upravljanje korisnicima',
        'main_heading'         => 'Svi korisnici',
        'sub_heading'          => 'Pregledaj, pretraži i obriši bilo koji nalog u sistemu. Samo supreme admin može pristupiti ovoj stranici.',
        'search_placeholder'   => 'Pretraga po imenu ili email-u…',
        'search_button'        => 'Pretraži',
        'clear_button'         => 'Obriši',
        'all_roles_option'     => 'Sve uloge',
        'empty_results'        => 'Nema korisnika koji odgovaraju trenutnim filterima.',
        'empty_results_hint'   => 'Pokušaj da obrišeš pretragu ili izabereš drugu ulogu.',

        'role_supreme'         => 'Supreme',
        'role_superadmin'      => 'Superadmin',
        'role_admin'           => 'Admin',
        'role_promoter'        => 'Promoter',
        'role_promoter_manager'=> 'Promoter menadžer',
        'role_sub_promoter'    => 'Promoter',

        'table' => [
            'header_name'         => 'Ime',
            'header_role'         => 'Uloga',
            'header_parent'       => 'Menadžer',
            'header_orders'       => 'Narudžbine',
            'header_subs'         => 'Promoteri',
            'header_joined_date'  => 'Pridružen',
            'header_actions'      => 'Akcije',
        ],

        'action_delete'        => 'Obriši',
        'action_locked'        => 'Zaključano',
        'delete_confirm_message' => 'Trajno obrisati :name? Ovo se ne može opozvati.',
    ],
];