<script setup>
import 'vue3-carousel/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'

const carouselConfig = {
    itemsToShow: 1,
    wrapAround: true
}
</script>

<template>
    <Carousel v-bind="carouselConfig">
        <Slide v-for="slide in 10" :key="slide">
            <div class="flex flex-col items-center justify-center">
                <ui-heading>{{ slide }}</ui-heading>
                <ui-button size="sm">{{ slide }}</ui-button>
            </div>
        </Slide>

        <template #addons>
            <Pagination />
        </template>
    </Carousel>
</template>

<style>
.carousel__track {
    margin-bottom: 40px;
}
</style>
