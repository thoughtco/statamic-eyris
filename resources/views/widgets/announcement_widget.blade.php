@if (! empty($slides))
<ui-card>
    <announcement-carousel
        slides='@json($slides)'
    />
</ui-card>
@endif
