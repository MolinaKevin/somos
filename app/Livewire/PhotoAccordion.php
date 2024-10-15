<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Foto;
use App\Models\Commerce;
use App\Models\Nro;

class PhotoAccordion extends Component
{
    public $expandedItem = null; // Solo un elemento expandido

    public function toggle($fotableId)
    {
        if ($this->expandedItem === $fotableId) {
            $this->expandedItem = null;
        } else {
            $this->expandedItem = $fotableId;
        }
    }

    public function render()
    {
        // Obtener todas las fotos con su relación fotable (comercio o NRO)
        $fotos = Foto::with(['fotable'])->get();

        // Agrupar las fotos por el ID del modelo relacionado (comercio o NRO)
        $commerces = Commerce::getCommercesWithPhotos();
        $nros = Nro::getNrosWithPhotos();
        $entitys = $commerces->merge($nros);

        return view('livewire.photo-accordion', [
            'entitys' => $entitys,
        ]);
    }
}

