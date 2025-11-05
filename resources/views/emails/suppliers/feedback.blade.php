{{-- resources/views/emails/suppliers/feedback.blade.php --}}
<p>Hola {{ $supplier->company_name }},</p>

<p>Te compartimos retroalimentación sobre el documento/tipo <strong>{{ $type }}</strong>:</p>

<blockquote style="border-left:4px solid #ccc;padding-left:10px;color:#555;">
    {{ $feedback }}
</blockquote>

<p>
    <strong>Enviado por:</strong> {{ $sender->name ?? '—' }}
    @if(!empty($sender->email))
        ({{ $sender->email }})
    @endif
</p>

<p>Por favor, ingresa al portal para atender esta observación.</p>

<p>Atentamente,<br>Equipo de TotalGas</p>
