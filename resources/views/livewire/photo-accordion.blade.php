<div>
    <h2>Fotos</h2>

    <!-- Mensaje de éxito -->
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

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
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Path</th>
                                        <th>Fondo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($entity->fotos as $foto)
                                        <tr>
                                            <td>
                                                <img src="{{ asset($foto->url) }}" alt="Foto" class="img-thumbnail" style="width: 100px; height: auto;">
                                            </td>
                                            <td>{{ $foto->url }}</td>
                                            <td>
                                                @if ($foto->url == $entity->background_image)
                                                    Sí
                                                @else
                                                    No
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-danger" wire:click="confirmDelete({{ $foto->id }})">Eliminar</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

