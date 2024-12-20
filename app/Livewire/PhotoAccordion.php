<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Foto;
use App\Models\Commerce;
use App\Models\Nro;

class PhotoAccordion extends Component
{
    public $expandedItem = null;
    public $photoIdToDelete = null;

    public function toggle($id)
    {
        $this->expandedItem = $this->expandedItem === $id ? null : $id;
    }

    public function render()
    {
        
        $fotos = Foto::with(['fotable'])->get();

        
        $commerces = Commerce::getCommercesWithPhotos();
        $nros = Nro::getNrosWithPhotos();
        $entitys = $commerces->merge($nros);

        return view('livewire.photo-accordion', [
            'entitys' => $entitys,
        ]);
    }

    public function confirmDelete($fotoId)
    {
        $foto = Foto::find($fotoId);
        if ($foto) {
            $foto->delete(); 
            session()->flash('message', 'Foto eliminada exitosamente.');
        }
        $this->photoIdToDelete = null; 
    }
}

