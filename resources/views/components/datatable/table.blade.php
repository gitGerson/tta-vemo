<div class="{{ $responsiveClass }}">
    <table id="{{ $id }}" class="{{ $class }}">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
    </table>
</div>
