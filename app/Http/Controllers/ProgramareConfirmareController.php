<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Programare;

use App\Traits\TrimiteSmsTrait;

class ProgramareConfirmareController extends Controller
{
    use TrimiteSmsTrait;

    // Trimite SMS la comanda
    public function cerereConfirmareSms(Request $request, Programare $programare)
    {
        $mesaj = 'Accesati ' . url('/status-programare/' . $programare->cheie_unica) . ', pentru a confirma sau anula programarea din ' . Carbon::parse($programare->data_ora_programare)->isoFormat('DD.MM.YYYY') .
                    ', ora ' . Carbon::parse($programare->data_ora_programare)->isoFormat('HH:mm') .
                    '. AutoGNS!';
        // Referitor la diacritice, puteti face conversia unui string cu diacritice intr-unul fara diacritice, in mod automatizat cu aceasta functie PHP:
        $mesaj = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD)->transliterate($mesaj);
        $this->trimiteSms('programari', 'confirmare', $programare->id, [$programare->telefon], $mesaj);

        return back();
    }

    public function statusProgramare(Request $request, Programare $programare)
    {
        $confirmat_deja = false;
        if (Carbon::parse($programare->data_ora_programare)->greaterThan(Carbon::now())){
            if ($request->has('confirmare')){
                if ($request->confirmare === 'da'){
                    $programare->confirmare = 1;
                    $programare->confirmare_client_timestamp = Carbon::now();
                    $programare->save();
                }
                if ($request->confirmare === 'nu'){
                    $programare->confirmare = 0;
                    $programare->confirmare_client_timestamp = Carbon::now();
                    $programare->save();
                }
            $confirmat_deja = true;
            }
        }

        return view('programari.diverse.confirmareInfirmareProgramare', compact('programare', 'confirmat_deja'));
    }
}
