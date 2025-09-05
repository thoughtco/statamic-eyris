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
    <Carousel v-bind="carouselConfig">
        <Slide v-for="slide in slides">
            <div class="flex flex-col items-center justify-center">
                <ui-heading v-text="slide.title" size="xl" class="mb-1" />
                <ui-description v-html="slide.content" />
                <ui-button
                    class="mt-4"
                    :href="slide.button?.link"
                    size="sm"
                    v-show="slide.button"
                    v-text="slide.button?.text"
                />
            </div>
        </Slide>

        <template #addons>
            <Pagination />
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

.carousel__track {
     margin-bottom: 60px;
}
</style>
