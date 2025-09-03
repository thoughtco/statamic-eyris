import AnnouncementCarousel from './components/AnnouncementCarousel.vue';

Statamic.booting(() => {
    Statamic.$components.register('announcement-carousel', AnnouncementCarousel);
});
