<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    @if (isset($message) && method_exists($message, 'embed'))
        <img src="{{ $message->embed(public_path('icons/reso.png')) }}" class="logo" alt="Reso Logo" style="max-height: 75px; width: auto;">
    @else
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('icons/reso.png'))) }}" class="logo" alt="Reso Logo" style="max-height: 75px; width: auto;">
    @endif
</a>
</td>
</tr>
