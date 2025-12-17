@php($currentId = session('business_id'))
<form action="{{ route('business.switch') }}" method="POST" class="d-flex align-items-center gap-2">
  @csrf
  <select name="business_id" class="form-select form-select-sm" style="width:auto">
    @foreach(auth()->user()->businesses as $biz)
      <option value="{{ $biz->id }}" {{ empty($currentId) ? ($loop->first ? 'selected' : '') : ($currentId==$biz->id ? 'selected' : '') }}>{{ $biz->name }}</option>
    @endforeach
  </select>
  <button class="btn btn-sm btn-outline-primary" type="submit">Switch</button>
</form>