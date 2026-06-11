import './bootstrap';
import '@getartisanflow/alpineflow/css';

import Alpine from 'alpinejs';
import AlpineFlow from '@getartisanflow/alpineflow';

window.Alpine = Alpine;
Alpine.plugin(AlpineFlow);
Alpine.start();
