
@section('main')

	<h2>titre : {{ $model->title }}</h2>

	@include('public.files._list', array('files' => $model->files))

@stop
