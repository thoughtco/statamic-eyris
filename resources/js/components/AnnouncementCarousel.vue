<script setup>
import 'vue3-carousel/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'

const carouselConfig = {
    itemsToShow: 1,
    slideEffect: 'fade',
    wrapAround: true
}

const props = defineProps(['slides']);

const slides = JSON.parse(props.slides);
</script>

<template>
    <Carousel v-bind="carouselConfig" :style="'padding-bottom: ' + (slides.length > 1 ? '42px' : '10px')">
        <Slide v-for="slide in slides">
            <div class="flex flex-col items-center justify-center">
                <ui-heading v-text="slide.title" size="xl" class="mb-1" />
                <ui-description v-html="slide.content" />
            </div>
        </Slide>

        <template #addons>
            <Pagination v-show="slides.length > 1" />
        </template>
    </Carousel>
</template>

<style>
.carousel {
    --vc-pgn-background-color: var(--theme-color-gray-500);
    --vc-pgn-active-color: var(--theme-color-primary);
    --vc-pgn-border-radius: 6px;
    --vc-pgn-height: 12px;
    --vc-pgn-width: 12px;
}
</style>
