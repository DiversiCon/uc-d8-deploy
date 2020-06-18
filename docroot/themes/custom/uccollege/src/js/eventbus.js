// This is the global event bus for Vue components.
// It's used to pass events around from any component to
// another, without being restricted to just parents/children
import Vue from 'vue';

const EventBus = new Vue();

export default EventBus;
