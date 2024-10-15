<div>
    <h2>Fotos</h2>

    <div class="accordion" id="photoAccordion">
        @foreach ($entitys as $entity)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $entity->id }}">
                    <button class="accordion-button" type="button" wire:click="toggle('{{ $entity->id }}')">
                        {{ $entity->name ?? 'Sin Nombre' }} <!-- Mostrar nombre del comercio o entidad -->
                    </button>
                </h2>
                @if ($expandedItem == $entity->id)
                    <div id="collapse{{ $entity->id }}" class="accordion-collapse" aria-labelledby="heading{{ $entity->id }}" data-bs-parent="#photoAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                @foreach ($entity->fotos as $foto)
                                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                                        <div class="photo-item">
                                            <img src="{{ asset($foto->url) }}" alt="Foto" class=" img-thumbnail">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

