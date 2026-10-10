export default function scopeMapStyles() {
    return {
        postcssPlugin: 'partsmall-scope-map-styles',
        Once(root) {
            root.walkRules((rule) => {
                if (! /(?:^|[\\/])NeshanMapboxGl\.css$/.test(rule.source?.input.file ?? '')) {
                    return;
                }

                // Nested selectors already inherit their parent's scope.
                for (let parent = rule.parent; parent; parent = parent.parent) {
                    if (parent.type === 'rule' || /keyframes$/i.test(parent.name ?? '')) {
                        return;
                    }
                }

                rule.selectors = rule.selectors.map((selector) => (
                    selector.startsWith('.mapboxgl-map')
                        ? selector
                        : `.mapboxgl-map :is(${selector})`
                ));
            });
        },
    };
}
