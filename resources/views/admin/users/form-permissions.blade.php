@php($allPerms = config('permissions'))
<div class="card mt-3">
    <div class="card-header">Access Permissions</div>
    <div class="card-body">
        <div class="row">
            @foreach($allPerms as $key => $label)
                <div class="col-12 col-md-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}"
                               id="perm_{{ $key }}"
                               @checked(in_array($key, old('permissions', $user->permissions ?? [])))>
                        <label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
