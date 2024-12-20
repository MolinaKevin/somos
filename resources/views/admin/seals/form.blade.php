<!-- resources/views/admin/seals/form.blade.php -->

@if(isset($seal))
    <form action="{{ route('admin.seals.update', $seal->id) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
@else
    <form action="{{ route('admin.seals.store') }}" method="POST" enctype="multipart/form-data">
@endif
    @csrf

    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $seal->name ?? '') }}" required>
        @error('name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="slug">Slug (optional):</label>
        <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $seal->slug ?? '') }}">
        @error('slug')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    @if(isset($seal) && $seal->image)
        <div class="form-group">
            <label>Current Image:</label><br>
            <img src="{{ asset('storage/' . $seal->image) }}" alt="{{ $seal->name }}" width="100">
        </div>
    @endif

    <div class="form-group">
        <label for="image">Image (optional):</label>
        <input type="file" name="image" id="image" class="form-control-file">
        @error('image')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-success">{{ isset($seal) ? 'Update' : 'Create' }} Seal</button>
    <a href="{{ route('admin.seals.index') }}" class="btn btn-secondary">Cancel</a>
</form>

