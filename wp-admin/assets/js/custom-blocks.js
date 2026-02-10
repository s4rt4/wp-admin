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
        category: 'Sections',
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
        category: 'Sections',
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

    // 7. Basic List (User Requested)
    bm.add('basic-list', {
        label: 'List',
        category: 'Basic',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M7 5h14v2H7V5zm0 8h14v2H7v-2zm0 8h14v2H7v-2zM3 6h2v2H3V6zm0 8h2v2H3v-2zm0 8h2v2H3v-2z"/></svg>',
        content: `
            <ul style="padding-left: 2rem; margin-bottom: 1rem;">
                <li>List item 1</li>
                <li>List item 2</li>
                <li>List item 3</li>
            </ul>
        `
    });

    // 8. Media List (Image Left + Text Right)
    bm.add('media-list', {
        label: 'Media List',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 6h3v12H4V6zm5 2h11v2H9V8zm0 6h11v2H9v-2z"/></svg>',
        content: `
            <div class="media-list-container" style="padding: 1rem;">
                <!-- Item 1 -->
                <div class="media-list-item" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start;">
                    <div style="flex: 0 0 100px; max-width: 100px;">
                        <img src="https://via.placeholder.com/100" style="width: 100%; height: auto; border-radius: 4px; object-fit: cover;" alt="Image">
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">Title Here</h3>
                        <p style="margin: 0; color: #555;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="media-list-item" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start;">
                    <div style="flex: 0 0 100px; max-width: 100px;">
                        <img src="https://via.placeholder.com/100" style="width: 100%; height: auto; border-radius: 4px; object-fit: cover;" alt="Image">
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">Title Here</h3>
                        <p style="margin: 0; color: #555;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>
                    </div>
                </div>
            </div>
        `
    });

    // 9. Styled Form (User Requested)
    bm.add('styled-form', {
        label: 'Styled Form',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M19,3H5C3.9,3 3,3.9 3,5V19C3,20.1 3.9,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.9 20.1,3 19,3M19,19H5V5H19V19M7,7H17V9H7V7M7,11H17V13H7V11M7,15H13V17H7V15Z" /></svg>',
        content: `
            <form class="styled-form" style="padding: 2rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 500px; margin: 0 auto;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: #2d3748; text-align: center;">Contact Us</h3>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #4a5568; font-weight: 500;">Name</label>
                    <input type="text" name="name" placeholder="Your Name" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 1rem; color: #2d3748; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #4a5568; font-weight: 500;">Email</label>
                    <input type="email" name="email" placeholder="your@email.com" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 1rem; color: #2d3748; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #4a5568; font-weight: 500;">Subject</label>
                    <select name="subject" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 1rem; color: #2d3748; box-sizing: border-box; background-color: white;">
                        <option value="">Select a subject</option>
                        <option value="support">Support</option>
                        <option value="sales">Sales</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #4a5568; font-weight: 500;">Message</label>
                    <textarea name="message" rows="4" placeholder="Your message..." style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 1rem; color: #2d3748; box-sizing: border-box; font-family: inherit;"></textarea>
                </div>
                
                <button type="button" style="width: 100%; padding: 0.75rem; background-color: #4299e1; color: white; border: none; border-radius: 4px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s;">Send Message</button>
            </form>
        `
    });

    // 10. Icon List (User Requested)
    bm.add('icon-list', {
        label: 'Icon List',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M7 5h14v2H7V5zm0 8h14v2H7v-2zm0 8h14v2H7v-2zM3 6h2v2H3V6zm0 8h2v2H3v-2zm0 8h2v2H3v-2z"/></svg>',
        content: `
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="display: flex; align-items: center; margin-bottom: 0.75rem; color: #4a5568;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; background: #e6fffa; color: #38b2ac; border-radius: 50%; padding: 4px; margin-right: 0.75rem;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span>High quality component library</span>
                </li>
                <li style="display: flex; align-items: center; margin-bottom: 0.75rem; color: #4a5568;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; background: #e6fffa; color: #38b2ac; border-radius: 50%; padding: 4px; margin-right: 0.75rem;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span>Easy to customize with CSS</span>
                </li>
                <li style="display: flex; align-items: center; margin-bottom: 0.75rem; color: #4a5568;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; background: #e6fffa; color: #38b2ac; border-radius: 50%; padding: 4px; margin-right: 0.75rem;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span>Responsive and mobile ready</span>
                </li>
            </ul>
        `
    });

    // 11. Review Section (User Requested)
    bm.add('review-section', {
        label: 'Reviews',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"/></svg>',
        content: `
            <div style="padding: 3rem 1rem; background-color: #f9fafb;">
                <h2 style="text-align: center; margin-bottom: 2.5rem; color: #1a202c; font-size: 2rem;">Customer Reviews</h2>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem;">
                    <!-- Review Card 1 -->
                    <div style="flex: 1 1 300px; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="color: #ecc94b; margin-bottom: 0.75rem;">★★★★★</div>
                        <p style="color: #4a5568; font-style: italic; margin-bottom: 1.25rem;">"Absolutely amazing! The features are top-notch and the support team is incredible."</p>
                        <div style="display: flex; align-items: center;">
                             <img src="https://via.placeholder.com/40" style="border-radius: 50%; margin-right: 0.75rem;" alt="User">
                             <div>
                                 <strong style="display: block; color: #2d3748; font-size: 0.9rem;">John Smith</strong>
                                 <span style="font-size: 0.8rem; color: #718096;">Marketing Director</span>
                             </div>
                        </div>
                    </div>
                    <!-- Review Card 2 -->
                    <div style="flex: 1 1 300px; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="color: #ecc94b; margin-bottom: 0.75rem;">★★★★★</div>
                        <p style="color: #4a5568; font-style: italic; margin-bottom: 1.25rem;">"Great value for money. It has helped us streamline our workflow significantly."</p>
                        <div style="display: flex; align-items: center;">
                             <img src="https://via.placeholder.com/40" style="border-radius: 50%; margin-right: 0.75rem;" alt="User">
                             <div>
                                 <strong style="display: block; color: #2d3748; font-size: 0.9rem;">Sarah Johnson</strong>
                                 <span style="font-size: 0.8rem; color: #718096;">Small Business Owner</span>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        `
    });

    // 12. Carousel Slider (CSS Only)
    bm.add('carousel-slider', {
        label: 'Carousel',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M7 19h10V4H7v15zm-5-2h4V6H2v11zM18 6v11h4V6h-4z"/></svg>',
        content: `
            <div class="carousel-container" style="position: relative; max-width: 800px; margin: 0 auto; overflow: hidden; border-radius: 8px;">
                <div class="carousel-slides" style="display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
                    <!-- Slide 1 -->
                    <div class="carousel-slide" style="flex: 0 0 100%; scroll-snap-align: center; position: relative;">
                        <img src="https://via.placeholder.com/800x400/4299e1/ffffff?text=Slide+1" style="width: 100%; display: block;" alt="Slide 1">
                        <div style="position: absolute; bottom: 20px; left: 20px; color: white; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 4px;">
                            <h3 style="margin: 0;">Amazing Slide 1</h3>
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="carousel-slide" style="flex: 0 0 100%; scroll-snap-align: center; position: relative;">
                        <img src="https://via.placeholder.com/800x400/48bb78/ffffff?text=Slide+2" style="width: 100%; display: block;" alt="Slide 2">
                        <div style="position: absolute; bottom: 20px; left: 20px; color: white; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 4px;">
                            <h3 style="margin: 0;">Incredible Slide 2</h3>
                        </div>
                    </div>
                    <!-- Slide 3 -->
                    <div class="carousel-slide" style="flex: 0 0 100%; scroll-snap-align: center; position: relative;">
                        <img src="https://via.placeholder.com/800x400/ed8936/ffffff?text=Slide+3" style="width: 100%; display: block;" alt="Slide 3">
                        <div style="position: absolute; bottom: 20px; left: 20px; color: white; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 4px;">
                            <h3 style="margin: 0;">Fantastic Slide 3</h3>
                        </div>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 0.5rem; color: #718096; font-size: 0.8rem;">
                    (Scroll horizontally to see more)
                </div>
            </div>
        `
    });

    // 13. Hero Section (2 Columns)
    bm.add('hero-2-col', {
        label: 'Hero (2 Col)',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 5h16v14H4V5zm2 2v10h5V7H6zm7 0v10h5V7h-5z"/></svg>',
        content: `
            <section class="hero-2-col" style="padding: 4rem 2rem; background-color: #f7fafc; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 2rem;">
                <!-- Left Column: Content -->
                <div style="flex: 1 1 400px; padding-right: 1rem;">
                    <h1 style="font-size: 3rem; margin-bottom: 1.5rem; color: #1a202c; line-height: 1.2;">Design Better Websites Faster</h1>
                    <p style="font-size: 1.25rem; color: #4a5568; margin-bottom: 2rem; line-height: 1.6;">Build responsive, mobile-first projects on the web with the world's most popular front-end component library.</p>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="background-color: #3182ce; color: white; padding: 0.75rem 2rem; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 1.1rem;">Get Started</a>
                        <a href="#" style="background-color: transparent; color: #3182ce; padding: 0.75rem 2rem; text-decoration: none; border: 2px solid #3182ce; border-radius: 4px; font-weight: 600; font-size: 1.1rem;">Learn More</a>
                    </div>
                </div>
                
                <!-- Right Column: Image -->
                <div style="flex: 1 1 400px; display: flex; justify-content: center;">
                    <img src="https://via.placeholder.com/600x400" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);" alt="Hero Image">
                </div>
            </section>
        `
    });

    // 14. Search Bar (User Requested)
    bm.add('search-bar', {
        label: 'Search Bar',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>',
        content: `
            <form action="index.php" method="get" class="search-bar-block" style="display: flex; max-width: 400px; width: 100%; gap: 0.5rem; margin: 1rem 0;">
                <input type="text" name="search" placeholder="Search..." style="flex: 1; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 1rem; outline: none; transition: border-color 0.2s;">
                <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #4299e1; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s;">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
        `
    });

    // 15. Navbar Responsive (Logo + Dropdown + Hamburger)
    bm.add('custom-navbar', {
        label: 'Navbar (Responsive)',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21 5H3v14h18V5zm-2 12H5V7h14v10zM7 9h10v2H7V9z"/></svg>',
        content: `
            <header class="custom-navbar-block" style="background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100;">
                <style>
                    .nav-container { display: flex; align-items: center; justify-content: space-between; padding: 1rem 2rem; max-width: 1200px; margin: 0 auto; flex-wrap: wrap; }
                    .nav-menu { display: flex; gap: 1.5rem; list-style: none; margin: 0; padding: 0; }
                    .nav-item { position: relative; }
                    .nav-link { text-decoration: none; color: #4a5568; font-weight: 500; font-size: 1rem; transition: color 0.2s; }
                    .nav-link:hover { color: #4299e1; }
                    .nav-toggle-label { display: none; cursor: pointer; }
                    .nav-toggle { display: none; }
                    
                    /* Dropdown */
                    .dropdown-content { display: none; position: absolute; top: 100%; left: 0; background: white; min-width: 150px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 4px; padding: 0.5rem 0; z-index: 20; }
                    .dropdown-item { display: block; padding: 0.5rem 1rem; color: #4a5568; text-decoration: none; }
                    .dropdown-item:hover { background-color: #f7fafc; color: #4299e1; }
                    .nav-item:hover .dropdown-content { display: block; }
                    
                    @media (max-width: 768px) {
                        .nav-menu { display: none; width: 100%; flex-direction: column; gap: 0; margin-top: 1rem; }
                        .nav-menu li { width: 100%; }
                        .nav-link { display: block; padding: 0.75rem 0; border-bottom: 1px solid #edf2f7; }
                        .nav-toggle-label { display: block; }
                        #nav-toggle:checked ~ .nav-menu { display: flex; }
                        .dropdown-content { position: static; box-shadow: none; padding-left: 1rem; display: none; }
                        .nav-item:hover .dropdown-content { display: block; }
                    }
                </style>
                
                <div class="nav-container">
                    <!-- Logo -->
                    <a href="#" style="font-size: 1.5rem; font-weight: bold; color: #2d3748; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 32px; height: 32px; color: #4299e1;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5 10 5 10-5-5-2.5-5 2.5z"/></svg>
                        <span>Brand</span>
                    </a>
                    
                    <!-- Hamburger Checkbox Hack -->
                    <input type="checkbox" id="nav-toggle" class="nav-toggle">
                    <label for="nav-toggle" class="nav-toggle-label">
                        <svg style="width: 24px; height: 24px; color: #4a5568;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </label>
                    
                    <!-- Menu -->
                    <ul class="nav-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">About</a></li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" style="display: flex; align-items: center; gap: 4px;">Services <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></a>
                            <div class="dropdown-content">
                                <a href="#" class="dropdown-item">Web Design</a>
                                <a href="#" class="dropdown-item">SEO</a>
                                <a href="#" class="dropdown-item">Marketing</a>
                            </div>
                        </li>
                        <li class="nav-item"><a href="#" class="nav-link">Contact</a></li>
                    </ul>
                </div>
            </header>
        `
    });

    // 16. Gallery Grid (3 Columns x 2 Rows, Hover Overlay)
    bm.add('gallery-grid', {
        label: 'Gallery (3x2)',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 11h5V5H4v6zm0 7h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-7h5V5h-5v6zm6-6v6h5V5h-5z"/></svg>',
        content: `
            <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; padding: 2rem;">
                <!-- Generate 6 items -->
                <div class="gallery-item" style="overflow: hidden; border-radius: 8px; cursor: pointer; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <img src="https://via.placeholder.com/400x300?text=Image+1" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Gallery Image 1">
                    <div class="gallery-caption" style="padding: 1rem; text-align: center;">
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #333;">Image Title 1</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">Description of the image goes here.</p>
                    </div>
                </div>
                <div class="gallery-item" style="overflow: hidden; border-radius: 8px; cursor: pointer; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <img src="https://via.placeholder.com/400x300?text=Image+2" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Gallery Image 2">
                    <div class="gallery-caption" style="padding: 1rem; text-align: center;">
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #333;">Image Title 2</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">Description of the image goes here.</p>
                    </div>
                </div>
                <div class="gallery-item" style="overflow: hidden; border-radius: 8px; cursor: pointer; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <img src="https://via.placeholder.com/400x300?text=Image+3" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Gallery Image 3">
                    <div class="gallery-caption" style="padding: 1rem; text-align: center;">
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #333;">Image Title 3</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">Description of the image goes here.</p>
                    </div>
                </div>
                <div class="gallery-item" style="overflow: hidden; border-radius: 8px; cursor: pointer; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <img src="https://via.placeholder.com/400x300?text=Image+4" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Gallery Image 4">
                    <div class="gallery-caption" style="padding: 1rem; text-align: center;">
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #333;">Image Title 4</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">Description of the image goes here.</p>
                    </div>
                </div>
                <div class="gallery-item" style="overflow: hidden; border-radius: 8px; cursor: pointer; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <img src="https://via.placeholder.com/400x300?text=Image+5" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Gallery Image 5">
                    <div class="gallery-caption" style="padding: 1rem; text-align: center;">
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #333;">Image Title 5</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">Description of the image goes here.</p>
                    </div>
                </div>
                <div class="gallery-item" style="overflow: hidden; border-radius: 8px; cursor: pointer; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <img src="https://via.placeholder.com/400x300?text=Image+6" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Gallery Image 6">
                    <div class="gallery-caption" style="padding: 1rem; text-align: center;">
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #333;">Image Title 6</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">Description of the image goes here.</p>
                    </div>
                </div>
            </div>
        `
    });

    // 17. Post Grid (Blog Cards)
    bm.add('post-grid', {
        label: 'Post Grid',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>',
        content: `
            <div class="post-grid" style="display: flex; flex-wrap: wrap; gap: 2rem; padding: 2rem; justify-content: center;">
                <!-- Post Card 1 -->
                <article style="flex: 1 1 300px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <img src="https://via.placeholder.com/600x400" style="width: 100%; height: 200px; object-fit: cover;" alt="Post Cover">
                    <div style="padding: 1.5rem;">
                        <span style="font-size: 0.8rem; color: #718096; text-transform: uppercase; letter-spacing: 1px;">Technology</span>
                        <h3 style="margin: 0.5rem 0 1rem; font-size: 1.5rem; color: #2d3748;">The Future of Web Design</h3>
                        <div style="font-size: 0.875rem; color: #a0aec0; margin-bottom: 1rem;">
                            <span>Feb 10, 2026</span> • <span>By John Doe</span>
                        </div>
                        <p style="color: #4a5568; margin-bottom: 1.5rem; line-height: 1.6;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        <a href="#" style="display: inline-block; padding: 0.5rem 1rem; border: 1px solid #4299e1; color: #4299e1; border-radius: 4px; text-decoration: none; font-weight: 500; transition: all 0.2s;">Read More</a>
                    </div>
                </article>
                <!-- Post Card 2 -->
                <article style="flex: 1 1 300px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <img src="https://via.placeholder.com/600x400" style="width: 100%; height: 200px; object-fit: cover;" alt="Post Cover">
                    <div style="padding: 1.5rem;">
                        <span style="font-size: 0.8rem; color: #718096; text-transform: uppercase; letter-spacing: 1px;">Lifestyle</span>
                        <h3 style="margin: 0.5rem 0 1rem; font-size: 1.5rem; color: #2d3748;">10 Tips for Productivity</h3>
                        <div style="font-size: 0.875rem; color: #a0aec0; margin-bottom: 1rem;">
                            <span>Feb 08, 2026</span> • <span>By Sarah Jane</span>
                        </div>
                        <p style="color: #4a5568; margin-bottom: 1.5rem; line-height: 1.6;">Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        <a href="#" style="display: inline-block; padding: 0.5rem 1rem; border: 1px solid #4299e1; color: #4299e1; border-radius: 4px; text-decoration: none; font-weight: 500; transition: all 0.2s;">Read More</a>
                    </div>
                </article>
                <!-- Post Card 3 -->
                <article style="flex: 1 1 300px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <img src="https://via.placeholder.com/600x400" style="width: 100%; height: 200px; object-fit: cover;" alt="Post Cover">
                    <div style="padding: 1.5rem;">
                        <span style="font-size: 0.8rem; color: #718096; text-transform: uppercase; letter-spacing: 1px;">Travel</span>
                        <h3 style="margin: 0.5rem 0 1rem; font-size: 1.5rem; color: #2d3748;">Exploring the Mountains</h3>
                        <div style="font-size: 0.875rem; color: #a0aec0; margin-bottom: 1rem;">
                            <span>Feb 05, 2026</span> • <span>By Mike Smith</span>
                        </div>
                        <p style="color: #4a5568; margin-bottom: 1.5rem; line-height: 1.6;">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                        <a href="#" style="display: inline-block; padding: 0.5rem 1rem; border: 1px solid #4299e1; color: #4299e1; border-radius: 4px; text-decoration: none; font-weight: 500; transition: all 0.2s;">Read More</a>
                    </div>
                </article>
            </div>
        `
    });

    // 18. Off-Canvas Menu (Slide-in Sidebar)
    bm.add('off-canvas-menu', {
        label: 'Off-Canvas Menu',
        category: 'Sections',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M3 4h18v16H3V4zm6 14h10V6H9v12zm-4-2h2V8H5v8z"/></svg>',
        content: `
            <div>
                <style>
                    .offcanvas-trigger-btn { padding: 0.75rem 1.5rem; background: #2d3748; color: white; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; }
                    .offcanvas-toggle { display: none; }
                    .offcanvas-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); opacity: 0; visibility: hidden; transition: all 0.3s; z-index: 999; }
                    .offcanvas-panel { position: fixed; top: 0; right: 0; width: 300px; height: 100%; background: white; transform: translateX(100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1000; box-shadow: -2px 0 5px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
                    
                    #offcanvas-toggle:checked ~ .offcanvas-overlay { opacity: 1; visibility: visible; }
                    #offcanvas-toggle:checked ~ .offcanvas-panel { transform: translateX(0); }
                    
                    .offcanvas-header { padding: 1.5rem; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; }
                    .offcanvas-close { cursor: pointer; background: none; border: none; color: #a0aec0; font-size: 1.5rem; line-height: 1; }
                    .offcanvas-close:hover { color: #4a5568; }
                    .offcanvas-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
                    .offcanvas-menu { list-style: none; padding: 0; margin: 0; }
                    .offcanvas-link { display: block; padding: 0.75rem 0; color: #4a5568; text-decoration: none; border-bottom: 1px solid #edf2f7; font-size: 1.1rem; }
                    .offcanvas-link:hover { color: #4299e1; padding-left: 0.5rem; transition: padding 0.2s; }
                </style>
                
                <input type="checkbox" id="offcanvas-toggle" class="offcanvas-toggle">
                
                <!-- Trigger Button -->
                <label for="offcanvas-toggle" class="offcanvas-trigger-btn">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    Open Menu
                </label>
                
                <!-- Overlay -->
                <label for="offcanvas-toggle" class="offcanvas-overlay"></label>
                
                <!-- Panel -->
                <aside class="offcanvas-panel">
                    <div class="offcanvas-header">
                        <h2 style="margin: 0; font-size: 1.25rem;">Menu</h2>
                        <label for="offcanvas-toggle" class="offcanvas-close">&times;</label>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="offcanvas-menu">
                            <li><a href="#" class="offcanvas-link">Home</a></li>
                            <li><a href="#" class="offcanvas-link">About Us</a></li>
                            <li><a href="#" class="offcanvas-link">Services</a></li>
                            <li><a href="#" class="offcanvas-link">Portfolio</a></li>
                            <li><a href="#" class="offcanvas-link">Contact</a></li>
                        </ul>
                        <div style="margin-top: 2rem;">
                            <h3 style="font-size: 1rem; color: #718096; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">Contact Info</h3>
                            <p style="color: #718096; font-size: 0.9rem; margin-bottom: 0.5rem;">email@example.com</p>
                            <p style="color: #718096; font-size: 0.9rem;">+1 (555) 123-4567</p>
                        </div>
                    </div>
                </aside>
            </div>
        `
    });
}
