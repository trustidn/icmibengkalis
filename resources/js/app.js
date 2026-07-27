import richEditor from './editor';
import brandMark from './brand-mark';
import shareProfile from './share-profile';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('richEditor', richEditor);
    window.Alpine.data('brandMark', brandMark);
    window.Alpine.data('shareProfile', shareProfile);
});
