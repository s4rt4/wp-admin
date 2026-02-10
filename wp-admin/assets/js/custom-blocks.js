/**
 * Custom Blocks for GrapesJS
 * Included in builder-grapesjs.php
 */

function addCustomBlocks(editor) {
    const bm = editor.BlockManager;

    // 1. Card Block (Existing)
    bm.add('card-block', {
        label: 'Card',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M20,2H4C2.9,2 2,2.9 2,4V20C2,21.1 2.9,22 4,22H20C21.1,22 22,21.1 22,20V4C22,2.9 21.1,2 20,2M20,20H4V13H20V20M20,11H4V4H20V11Z" /></svg>',
        category: 'Basic',
        content: {
            type: 'div',
            classes: ['card'],
            style: {
                'background-color': '#fff',
                'border': '1px solid #e2e8f0',
                'border-radius': '0.375rem',
                'overflow': 'hidden',
                'box-shadow': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'max-width': '300px',
                'margin': '1rem'
            },
            components: [
                {
                    tagName: 'img',
                    type: 'image',
                    attributes: { src: 'https://via.placeholder.com/350x200' },
                    style: { 'width': '100%', 'height': 'auto', 'display': 'block' }
                },
                {
                    tagName: 'div',
                    classes: ['card-body'],
                    style: { 'padding': '1.25rem' },
                    components: [
                        {
                            tagName: 'h3',
                            content: 'Card Title',
                            style: { 'margin-top': '0', 'margin-bottom': '0.5rem', 'font-size': '1.25rem', 'font-weight': '600' }
                        },
                        {
                            tagName: 'p',
                            content: 'Some quick example text to build on the card title and make up the bulk of the card\'s content.',
                            style: { 'margin-bottom': '1.25rem', 'color': '#4a5568' }
                        },
                        {
                            tagName: 'a',
                            content: 'Read More',
                            attributes: { href: '#' },
                            style: {
                                'display': 'inline-block',
                                'padding': '0.5rem 1rem',
                                'background-color': '#4299e1',
                                'color': 'white',
                                'text-decoration': 'none',
                                'border-radius': '0.25rem',
                                'font-size': '0.875rem'
                            }
                        }
                    ]
                }
            ]
        }
    });

    // 2. Hero Section (Existing)
    bm.add('hero-section', {
        label: 'Hero Section',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M2,2H22V22H2V2M4,4V20H20V4H4M6,6H18V10H6V6M6,12H18V14H6V12M6,16H18V18H6V16Z" /></svg>',
        category: 'Basic',
        content: `
            <section class="hero-section" style="padding: 4rem 2rem; text-align: center; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: #2d3748;">Welcome to My Website</h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; color: #718096; max-width: 600px; margin-left: auto; margin-right: auto;">This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
                <a href="#" style="background-color: #4299e1; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 0.25rem; font-size: 1rem; font-weight: 600;">Learn More</a>
            </section>
        `
    });

    // 3. Feature Grid (New)
    bm.add('feature-grid', {
        label: 'Feature Grid',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/></svg>',
        content: `
            <div style="display: flex; flex-wrap: wrap; justify-content: space-around; padding: 2rem; gap: 2rem;">
                <!-- Feature 1 -->
                <div style="flex: 1 1 300px; text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div style="font-size: 2rem; margin-bottom: 1rem; color: #4299e1;">★</div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem;">Feature One</h3>
                    <p style="color: #718096;">Brief description of this amazing feature causing users to fall in love.</p>
                </div>
                <!-- Feature 2 -->
                <div style="flex: 1 1 300px; text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div style="font-size: 2rem; margin-bottom: 1rem; color: #4299e1;">⚡</div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem;">Feature Two</h3>
                    <p style="color: #718096;">Another incredible feature that improves productivity and efficiency.</p>
                </div>
                <!-- Feature 3 -->
                <div style="flex: 1 1 300px; text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div style="font-size: 2rem; margin-bottom: 1rem; color: #4299e1;">🛡️</div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem;">Feature Three</h3>
                    <p style="color: #718096;">Safety first security feature that keeps everything protected.</p>
                </div>
            </div>
        `
    });

    // 4. Testimonial (New)
    bm.add('testimonial', {
        label: 'Testimonial',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6zm4 4h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>', // Generic chat bubble icon replacment
        content: `
            <div style="background-color: #f7fafc; padding: 3rem 2rem; text-align: center; border-radius: 8px; margin: 2rem 0;">
                <p style="font-size: 1.25rem; font-style: italic; color: #4a5568; margin-bottom: 1.5rem;">"This product completely transformed how we work. The intuitive interface and powerful features are exactly what we needed."</p>
                <div style="display: flex; align-items: center; justify-content: center;">
                    <img src="https://via.placeholder.com/50x50" style="border-radius: 50%; margin-right: 1rem; width: 50px; height: 50px;" alt="User">
                    <div style="text-align: left;">
                        <strong style="display: block; color: #2d3748;">Jane Doe</strong>
                        <span style="font-size: 0.875rem; color: #718096;">CEO, Tech Corp</span>
                    </div>
                </div>
            </div>
        `
    });

    // 5. Call to Action (New)
    bm.add('cta-section', {
        label: 'Call to Action',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>',
        content: `
            <div style="background: linear-gradient(90deg, #4299e1 0%, #667eea 100%); color: white; padding: 4rem 2rem; text-align: center; border-radius: 8px;">
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Ready to Get Started?</h2>
                <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9;">Join thousands of satisfied users today and take your project to the next level.</p>
                <a href="#" style="background-color: white; color: #4299e1; padding: 0.75rem 2rem; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">Sign Up Now</a>
            </div>
        `
    });
    
    // 6. Pricing Table (New)
    bm.add('pricing-table', {
        label: 'Pricing Table',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M7 2v20h10V2H7zm2 2h6v2H9V4zm0 4h6v2H9V8zm0 4h6v2H9v-2zm0 4h6v2H9v-2z"/></svg>',
        content: `
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem; padding: 2rem;">
                <!-- Basic Plan -->
                <div style="flex: 1 1 250px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 2rem; text-align: center;">
                    <h3 style="color: #4a5568; margin-bottom: 0.5rem;">Basic</h3>
                    <div style="font-size: 2.5rem; font-weight: bold; color: #2d3748; margin-bottom: 1rem;">$19<span style="font-size: 1rem; color: #718096; font-weight: normal;">/mo</span></div>
                    <ul style="list-style: none; padding: 0; margin-bottom: 2rem; color: #4a5568;">
                        <li style="margin-bottom: 0.5rem;">5 Projects</li>
                        <li style="margin-bottom: 0.5rem;">Basic Analytics</li>
                        <li style="margin-bottom: 0.5rem;">Email Support</li>
                    </ul>
                    <a href="#" style="display: block; border: 1px solid #4299e1; color: #4299e1; padding: 0.75rem; text-decoration: none; border-radius: 4px; transition: all 0.2s;">Choose Basic</a>
                </div>
                
                <!-- Pro Plan -->
                <div style="flex: 1 1 250px; border: 1px solid #4299e1; border-radius: 8px; padding: 2rem; text-align: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <div style="background-color: #4299e1; color: white; display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold; margin-bottom: 1rem;">POPULAR</div>
                    <h3 style="color: #4a5568; margin-bottom: 0.5rem;">Pro</h3>
                    <div style="font-size: 2.5rem; font-weight: bold; color: #2d3748; margin-bottom: 1rem;">$49<span style="font-size: 1rem; color: #718096; font-weight: normal;">/mo</span></div>
                    <ul style="list-style: none; padding: 0; margin-bottom: 2rem; color: #4a5568;">
                        <li style="margin-bottom: 0.5rem;">Unlimited Projects</li>
                        <li style="margin-bottom: 0.5rem;">Advanced Analytics</li>
                        <li style="margin-bottom: 0.5rem;">Priority Support</li>
                    </ul>
                    <a href="#" style="display: block; background-color: #4299e1; color: white; padding: 0.75rem; text-decoration: none; border-radius: 4px;">Choose Pro</a>
                </div>
            </div>
        `
    });
}
