<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute muss akzeptiert werden.',
    'active_url' => ':attribute ist keine valide URL.',
    'after' => ':attribute muss ein Datum nach dem :date sein.',
    'alpha' => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => ':attribute darf nur Buchstaben, Zahlen und - enthalten.',
    'alpha_num' => ':attribute darf nur Buchstaben und Zahlen enthalten.',
    'array' => ':attribute muss ein Array sein.',
    'before' => ':attribute muss ein Datum vor dem :date sein.',
    'between' => [
        'numeric' => ':attribute muss zwischen :min und :max sein.',
        'file' => ':attribute muss zwischen :min und :max KB sein.',
        'string' => ':attribute muss zwischen :min und :max Zeichen haben.',
        'array' => ':attribute muss between :min und :max lang sein.',
    ],
    'boolean' => ':attribute field muss true oder false sein.',
    'confirmed' => ':attribute Bestätigung stimmt nicht überein.',
    'country' => ':attribute ist kein valides Land.',
    'date' => ':attribute ist kein valides Datum.',
    'date_format' => ':attribute stimm nicht mit dem Format :format überein.',
    'different' => ':attribute und :other müssen unterschiedlich sein.',
    'digits' => ':attribute muss :digits Zeichen lang sein.',
    'digits_between' => ':attribute muss zwischen :min und :max Zeichen lang sein.',
    'distinct' => ':attribute ist bereits vorhanden.',
    'email' => ':attribute muss eine valide E-Mail-Adresse sein.',
    'exists' => 'Das ausgewählte :attribute ist nicht valide.',
    'filled' => ':attribute muss ausgefüllt sein.',
    'image' => ':attribute muss ein Bild sein.',
    'in' => 'Das ausgewählte :attribute ist nicht valide.',
    'in_array' => ':attribute taucht nicht in :other auf.',
    'integer' => ':attribute muss eine Ganzzahl sein.',
    'ip' => ':attribute muss eine valide IP-Adresse sein.',
    'json' => ':attribute muss ein valider JSON-Sring sein.',
    'max' => [
        'numeric' => ':attribute darf nicht größer als :max sein.',
        'file' => ':attribute darf nicht größer als :max KB sein.',
        'string' => ':attribute darf nicht größer mehr :max Zeichen haben.',
        'array' => ':attribute darf nicht mehr als :max Elemente enthalten.',
    ],
    'mimes' => ':attribute muss eine Datei sein vom Typ: :values.',
    'min' => [
        'numeric' => ':attribute muss mindestens :min sein.',
        'file' => ':attribute muss mindestens :min kilobytes.',
        'string' => ':attribute muss mindestens :min Zeichen lang sein.',
        'array' => ':attribute muss mindestens :min Elemente enrhalten.',
    ],
    'not_in' => 'Das ausgewählte :attribute ist nicht valide.',
    'numeric' => ':attribute muss eine Zahl sein.',
    'present' => ':attribute muss vorhanden sein.',
    'regex' => ':attribute Format ist invalide.',
    'required' => ':attribute wird benötigt.',
    'required_if' => ':attribute wird benötigt, wenn :other :value ist.',
    'required_unless' => ':attribute wird benötigt, außer :other ist :values.',
    'required_with' => ':attribute wird benötigt, wenn :values vorhanden ist.',
    'required_with_all' => ':attribute wird benötigt, wenn :values vorhanden ist.',
    'required_without' => ':attribute wird benötigt, wenn :values nicht vorhanden ist.',
    'required_without_all' => ':attribute wird benötigt, nichts von: :values vorhanden ist.',
    'same' => ':attribute und :other müssen übereinstimmen.',
    'size' => [
        'numeric' => ':attribute muss :size groß sein.',
        'file' => ':attribute muss :size KB haben.',
        'string' => ':attribute muss :size Zeichen lang sein.',
        'array' => ':attribute muss :size Elemente enthalten.',
    ],
    'state' => 'Dieses Bundelsland / dieser Staat ist nicht valide für das ausgewählte Land.',
    'string' => ':attribute muss eine Zeichenkette sein.',
    'timezone' => ':attribute muss eine valide Zeitzone sein.',
    'unique' => ':attribute wird bereits verwendet.',
    'url' => ' Das Format von :attribute ist invalide.',
    'vat_id' => 'Diese USt-ID ist invalide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'team' => 'team'
    ],
];
