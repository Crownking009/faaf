-- =====================================================
-- FAAF Collections & Souvenirs - Demo Product Catalog
-- Run this AFTER database.sql.
--
-- This seed adds 10 demo products to each default category.
-- It is safe to re-run: existing products with the same slug are
-- left unchanged so you can edit demo rows into real products later.
--
-- Placeholder images are added only for products that do not yet have
-- any image row. Replace images anytime from the admin product form.
-- =====================================================

INSERT INTO products (category_id, name, slug, description, price, compare_price, gender, sizes, colors, stock, is_featured, is_new, status) VALUES

-- Jeans
((SELECT id FROM categories WHERE slug='jeans'), 'Classic Straight-Leg Jeans', 'classic-straight-leg-jeans', 'Everyday straight-leg denim with a comfortable mid-rise fit. Durable stitching built for daily wear.', 15500, 19000, 'unisex', '28,30,32,34,36', 'Blue,Black,Grey', 24, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Slim Fit Stretch Jeans', 'slim-fit-stretch-jeans', 'Stretch denim that moves with you with a slim leg and clean modern taper.', 17000, NULL, 'male', '30,32,34,36,38', 'Black,Indigo', 18, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'High-Rise Mom Jeans', 'high-rise-mom-jeans', 'Relaxed high-rise denim with a vintage-inspired shape and soft worn-in feel.', 16500, 20500, 'female', '26,28,30,32,34', 'Light Blue,Blue', 20, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Wide-Leg Denim Pants', 'wide-leg-denim-pants', 'Easy wide-leg jeans with a polished drape for dressed-up casual styling.', 18500, NULL, 'female', '28,30,32,34,36', 'Dark Blue,Black', 16, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Ripped Knee Jeans', 'ripped-knee-jeans', 'Casual denim with controlled knee distressing and a comfortable everyday waist.', 17500, 21000, 'unisex', '28,30,32,34,36,38', 'Blue,Washed Black', 14, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Bootcut Denim Jeans', 'bootcut-denim-jeans', 'Classic bootcut jeans with a flattering line from thigh to hem.', 16000, NULL, 'unisex', '28,30,32,34,36', 'Indigo,Black', 18, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Cargo Pocket Jeans', 'cargo-pocket-jeans', 'Utility denim with side cargo pockets and a relaxed streetwear fit.', 19500, 23500, 'male', '30,32,34,36,38', 'Blue,Black,Olive', 13, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Cropped Flare Jeans', 'cropped-flare-jeans', 'Cropped flare denim that pairs neatly with sandals, sneakers, or heels.', 16800, NULL, 'female', '26,28,30,32,34', 'Light Blue,White', 15, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Dark Wash Skinny Jeans', 'dark-wash-skinny-jeans', 'Clean dark wash skinny jeans with enough stretch for all-day comfort.', 17200, 20500, 'unisex', '28,30,32,34,36', 'Dark Blue,Black', 17, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jeans'), 'Relaxed Boyfriend Jeans', 'relaxed-boyfriend-jeans', 'Slouchy boyfriend denim with a laid-back fit and cuff-friendly hem.', 15800, NULL, 'female', '26,28,30,32,34,36', 'Blue,Grey', 19, 0, 1, 'active'),

-- T-Shirts
((SELECT id FROM categories WHERE slug='t-shirts'), 'Essential Crew Neck Tee', 'essential-crew-neck-tee', 'Soft cotton crew neck tee that works as a wardrobe staple.', 6500, 8000, 'unisex', 'S,M,L,XL,XXL', 'White,Black,Gold,Navy', 60, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Oversized Graphic Tee', 'oversized-graphic-tee', 'Relaxed oversized fit with a bold front print for casual styling.', 8500, NULL, 'unisex', 'S,M,L,XL', 'Black,White', 30, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'V-Neck Cotton T-Shirt', 'v-neck-cotton-t-shirt', 'Breathable V-neck tee with a neat shape for everyday layering.', 6200, NULL, 'unisex', 'S,M,L,XL,XXL', 'White,Black,Grey', 45, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Polo Collar T-Shirt', 'polo-collar-t-shirt', 'Smart casual polo tee with a soft collar and tidy button placket.', 9000, 11000, 'male', 'M,L,XL,XXL', 'Navy,Black,White', 28, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Cropped Baby Tee', 'cropped-baby-tee', 'Fitted cropped tee with a smooth stretch feel and clean neckline.', 7000, NULL, 'female', 'XS,S,M,L', 'Pink,White,Black', 25, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Longline Street Tee', 'longline-street-tee', 'Longline T-shirt with a modern drop shoulder and easy movement.', 7800, 9500, 'male', 'M,L,XL,XXL', 'Black,Sand,Grey', 24, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Ribbed Fitted Tee', 'ribbed-fitted-tee', 'Stretch ribbed tee with a body-skimming fit and polished texture.', 7500, NULL, 'female', 'S,M,L,XL', 'Cream,Black,Brown', 22, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Plain Pocket Tee', 'plain-pocket-tee', 'Minimal pocket tee in soft jersey fabric for casual everyday wear.', 6800, NULL, 'unisex', 'S,M,L,XL,XXL', 'White,Olive,Navy', 36, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Athletic Dry-Fit Tee', 'athletic-dry-fit-tee', 'Light performance tee with quick-dry fabric for active days.', 8200, 10000, 'unisex', 'S,M,L,XL,XXL', 'Black,Blue,Red', 32, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='t-shirts'), 'Striped Weekend Tee', 'striped-weekend-tee', 'Classic striped tee with a relaxed fit and soft weekend feel.', 7600, NULL, 'unisex', 'S,M,L,XL', 'White/Navy,Black/White', 27, 0, 0, 'active'),

-- Jean Skirts
((SELECT id FROM categories WHERE slug='jean-skirts'), 'A-Line Denim Skirt', 'a-line-denim-skirt', 'A flattering A-line cut in sturdy denim, finished with front pockets.', 12000, NULL, 'female', 'S,M,L,XL', 'Blue,Black', 15, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'High-Waist Denim Mini Skirt', 'high-waist-denim-mini-skirt', 'High-waist mini skirt with a fitted shape that pairs with any top.', 11500, 14000, 'female', 'S,M,L', 'Light Blue,Black', 12, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Button Front Denim Skirt', 'button-front-denim-skirt', 'Midi denim skirt with a button front and practical side pockets.', 13500, NULL, 'female', 'S,M,L,XL', 'Blue,Indigo', 16, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Frayed Hem Jean Skirt', 'frayed-hem-jean-skirt', 'Casual denim skirt with a raw hem detail and comfortable waist.', 11800, NULL, 'female', 'S,M,L,XL', 'Light Blue,Washed Black', 18, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Denim Pencil Skirt', 'denim-pencil-skirt', 'Polished pencil skirt in stretch denim for a sleek casual look.', 14500, 17000, 'female', 'S,M,L,XL', 'Dark Blue,Black', 14, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Pleated Denim Skort', 'pleated-denim-skort', 'Playful pleated skort with hidden shorts for easy movement.', 12800, NULL, 'female', 'XS,S,M,L', 'Blue,White', 13, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Patchwork Jean Skirt', 'patchwork-jean-skirt', 'Statement denim skirt with mixed wash patchwork panels.', 15000, 18500, 'female', 'S,M,L,XL', 'Multi Blue', 10, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Wrap Denim Skirt', 'wrap-denim-skirt', 'Wrap-style denim skirt with a secure side tie and relaxed shape.', 13200, NULL, 'female', 'S,M,L,XL', 'Blue,Black', 15, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Cargo Denim Mini Skirt', 'cargo-denim-mini-skirt', 'Utility mini skirt with cargo pockets and a structured denim feel.', 13800, 16500, 'female', 'S,M,L', 'Olive,Blue,Black', 12, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='jean-skirts'), 'Front Slit Midi Denim Skirt', 'front-slit-midi-denim-skirt', 'Midi denim skirt with a front slit for easy walking and modern styling.', 14800, NULL, 'female', 'S,M,L,XL', 'Indigo,Black', 11, 0, 0, 'active'),

-- Short Dresses
((SELECT id FROM categories WHERE slug='short-dresses'), 'Wrap Short Dress', 'wrap-short-dress', 'Soft wrap-style short dress with a flattering tie waist for warm days.', 14500, NULL, 'female', 'S,M,L,XL', 'Red,Gold,Black', 20, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Bodycon Party Dress', 'bodycon-party-dress', 'Figure-hugging bodycon dress designed for outings and events.', 16000, 19500, 'female', 'S,M,L', 'Black,Gold,Wine', 14, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Floral Mini Dress', 'floral-mini-dress', 'Light floral mini dress with soft sleeves and an easy daytime fit.', 15000, NULL, 'female', 'S,M,L,XL', 'Floral Pink,Floral Blue', 18, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Satin Slip Dress', 'satin-slip-dress', 'Smooth satin short dress with adjustable straps and a clean silhouette.', 17500, 21000, 'female', 'S,M,L', 'Champagne,Black,Emerald', 12, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Ruched Mesh Dress', 'ruched-mesh-dress', 'Ruched short dress with soft mesh overlay and a sculpted fit.', 16500, NULL, 'female', 'S,M,L,XL', 'Black,Wine,Nude', 15, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'T-Shirt Mini Dress', 't-shirt-mini-dress', 'Comfortable T-shirt dress with a relaxed shape for casual days.', 11000, NULL, 'female', 'S,M,L,XL', 'Black,Grey,Olive', 25, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Pleated Skater Dress', 'pleated-skater-dress', 'Short skater dress with a fitted waist and soft pleated skirt.', 15500, 18500, 'female', 'S,M,L,XL', 'Blue,Red,Black', 16, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Off-Shoulder Mini Dress', 'off-shoulder-mini-dress', 'Off-shoulder mini dress with a flattering neckline and secure fit.', 17000, NULL, 'female', 'S,M,L', 'White,Black,Pink', 13, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Denim Shirt Dress', 'denim-shirt-dress', 'Button-down denim shirt dress with a belt-friendly waist.', 18500, 22000, 'female', 'S,M,L,XL', 'Blue,Dark Blue', 10, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='short-dresses'), 'Lace Trim Short Dress', 'lace-trim-short-dress', 'Soft short dress with lace trim detail for a delicate finish.', 16800, NULL, 'female', 'S,M,L,XL', 'White,Black,Lilac', 14, 0, 0, 'active'),

-- Jalabia
((SELECT id FROM categories WHERE slug='jalabia'), 'Embroidered Jalabia', 'embroidered-jalabia', 'Premium flowing Jalabia with fine embroidery detail at the neckline.', 22000, 27000, 'male', 'M,L,XL,XXL', 'White,Black,Cream', 16, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Classic Plain Jalabia', 'classic-plain-jalabia', 'Simple breathable Jalabia in soft cotton-blend fabric for everyday comfort.', 17500, NULL, 'male', 'M,L,XL,XXL', 'White,Grey,Navy', 22, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Premium Collar Jalabia', 'premium-collar-jalabia', 'Structured collar Jalabia with subtle trim for a refined traditional look.', 24000, 28500, 'male', 'M,L,XL,XXL', 'White,Black,Olive', 14, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Short Sleeve Jalabia', 'short-sleeve-jalabia', 'Warm-weather Jalabia with short sleeves and a light easy fit.', 16500, NULL, 'male', 'M,L,XL,XXL', 'White,Blue,Grey', 20, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Kaftan Style Jalabia', 'kaftan-style-jalabia', 'Loose kaftan-inspired Jalabia with embroidery across the chest.', 26000, 31000, 'male', 'M,L,XL,XXL', 'Cream,Brown,Black', 12, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Contrast Trim Jalabia', 'contrast-trim-jalabia', 'Clean Jalabia with contrast piping for a modern finish.', 21000, NULL, 'male', 'M,L,XL,XXL', 'White/Black,Navy/Gold', 18, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Luxury Linen Jalabia', 'luxury-linen-jalabia', 'Breathable linen-blend Jalabia with a premium soft texture.', 28000, 33000, 'male', 'M,L,XL,XXL', 'Natural,White,Sage', 10, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Zip Front Jalabia', 'zip-front-jalabia', 'Modern zip-front Jalabia with a neat stand collar and side pockets.', 23000, NULL, 'male', 'M,L,XL,XXL', 'Black,Grey,Navy', 15, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Prayer Day Jalabia', 'prayer-day-jalabia', 'Comfortable Jalabia designed for mosque visits, Fridays, and celebrations.', 19500, NULL, 'male', 'M,L,XL,XXL', 'White,Cream,Blue', 21, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='jalabia'), 'Two-Tone Jalabia', 'two-tone-jalabia', 'Statement two-tone Jalabia with elegant panel placement.', 25000, 29500, 'male', 'M,L,XL,XXL', 'Black/Gold,White/Navy', 11, 0, 1, 'active'),

-- Abayah
((SELECT id FROM categories WHERE slug='abayah'), 'Classic Black Abayah', 'classic-black-abayah', 'Timeless flowing Abayah in premium fabric with subtle detailing.', 24000, NULL, 'female', 'S,M,L,XL', 'Black', 18, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Embellished Abayah', 'embellished-abayah', 'Statement Abayah with delicate stone embellishments along the sleeves and hem.', 29000, 34000, 'female', 'S,M,L,XL', 'Black,Navy', 10, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Open Front Kimono Abayah', 'open-front-kimono-abayah', 'Layer-friendly open front Abayah with soft drape and wide sleeves.', 26000, NULL, 'female', 'S,M,L,XL', 'Black,Mocha,Cream', 15, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Butterfly Sleeve Abayah', 'butterfly-sleeve-abayah', 'Elegant butterfly sleeve Abayah with a relaxed graceful silhouette.', 27500, 32000, 'female', 'S,M,L,XL', 'Black,Olive,Burgundy', 12, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Pleated Front Abayah', 'pleated-front-abayah', 'Pleated front Abayah with structured movement and a polished finish.', 28500, NULL, 'female', 'S,M,L,XL', 'Black,Grey', 13, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Lace Trim Abayah', 'lace-trim-abayah', 'Soft Abayah finished with lace trim at the cuffs and hem.', 30000, 36000, 'female', 'S,M,L,XL', 'Black,Nude', 9, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Everyday Jersey Abayah', 'everyday-jersey-abayah', 'Easy-care jersey Abayah made for comfort and repeat wear.', 21000, NULL, 'female', 'S,M,L,XL,XXL', 'Black,Navy,Brown', 22, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Beaded Occasion Abayah', 'beaded-occasion-abayah', 'Occasion Abayah with refined beadwork for celebrations and visits.', 34000, 39000, 'female', 'S,M,L,XL', 'Black,Emerald', 8, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Belted Abayah Dress', 'belted-abayah-dress', 'Abayah dress with matching belt for adjustable shape and coverage.', 25500, NULL, 'female', 'S,M,L,XL', 'Black,Taupe', 14, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='abayah'), 'Nida Fabric Abayah', 'nida-fabric-abayah', 'Smooth Nida-style fabric Abayah with a light flow and clean seams.', 32000, NULL, 'female', 'S,M,L,XL', 'Black,Maroon', 11, 0, 0, 'active'),

-- Shoes
((SELECT id FROM categories WHERE slug='shoes'), 'Classic Leather Sneakers', 'classic-leather-sneakers', 'Clean leather sneakers that go with denim, dresses, or native wear.', 21000, 25000, 'unisex', '39,40,41,42,43,44', 'White,Black', 25, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Formal Office Shoes', 'formal-office-shoes', 'Polished formal shoes crafted for comfort through long work days.', 26000, NULL, 'male', '40,41,42,43,44,45', 'Black,Brown', 14, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Chunky Platform Sneakers', 'chunky-platform-sneakers', 'Statement platform sneakers with padded support and bold profile.', 24500, 29500, 'female', '36,37,38,39,40,41', 'White,Black,Pink', 16, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Suede Loafers', 'suede-loafers', 'Soft suede loafers with an easy slip-on shape for smart casual wear.', 23500, NULL, 'male', '40,41,42,43,44,45', 'Brown,Navy,Black', 12, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Block Heel Pumps', 'block-heel-pumps', 'Comfortable block heel pumps for church, office, and events.', 22000, 26000, 'female', '36,37,38,39,40,41', 'Black,Nude,Gold', 15, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Running Knit Sneakers', 'running-knit-sneakers', 'Light knit sneakers with flexible soles for active everyday movement.', 19000, NULL, 'unisex', '38,39,40,41,42,43,44', 'Black,Grey,Blue', 28, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Ankle Strap Heels', 'ankle-strap-heels', 'Dressy ankle strap heels with a stable heel and clean finish.', 24000, 28500, 'female', '36,37,38,39,40,41', 'Black,Silver,Red', 11, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Canvas Low-Top Sneakers', 'canvas-low-top-sneakers', 'Casual canvas sneakers with a lightweight sole and simple styling.', 15000, NULL, 'unisex', '38,39,40,41,42,43,44', 'White,Black,Red', 30, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Oxford Lace-Up Shoes', 'oxford-lace-up-shoes', 'Classic lace-up Oxfords with a polished finish for formal outfits.', 28500, 33000, 'male', '40,41,42,43,44,45', 'Black,Brown', 10, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='shoes'), 'Fashion Ballet Flats', 'fashion-ballet-flats', 'Soft ballet flats with a neat round toe for daily comfort.', 16000, NULL, 'female', '36,37,38,39,40,41', 'Black,Nude,Gold', 19, 0, 1, 'active'),

-- Slippers
((SELECT id FROM categories WHERE slug='slippers'), 'Everyday Comfort Slides', 'everyday-comfort-slides', 'Cushioned slides for everyday comfort at home or out and about.', 5500, NULL, 'unisex', '38,39,40,41,42,43', 'Black,Gold,White', 40, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Designer Flat Sandals', 'designer-flat-sandals', 'Stylish flat sandals with a soft footbed for casual outings.', 8000, 9500, 'female', '36,37,38,39,40', 'Gold,Black', 20, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Leather Cross Strap Slippers', 'leather-cross-strap-slippers', 'Cross strap slippers with a leather-look finish and sturdy sole.', 10500, NULL, 'male', '40,41,42,43,44,45', 'Black,Brown', 18, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Beaded Ladies Slippers', 'beaded-ladies-slippers', 'Flat slippers with bead detail for easy dressy-casual styling.', 9000, 11000, 'female', '36,37,38,39,40,41', 'Gold,Silver,Black', 15, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Rubber Pool Slides', 'rubber-pool-slides', 'Water-friendly pool slides with grip soles and soft straps.', 4800, NULL, 'unisex', '38,39,40,41,42,43,44', 'Blue,Black,White', 35, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Double Buckle Sandals', 'double-buckle-sandals', 'Adjustable double buckle sandals with a molded comfort footbed.', 9500, NULL, 'unisex', '37,38,39,40,41,42,43,44', 'Black,Tan,White', 24, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Fur Strap Indoor Slippers', 'fur-strap-indoor-slippers', 'Soft faux-fur strap slippers made for cozy indoor lounging.', 7500, 9000, 'female', '36,37,38,39,40,41', 'Pink,Black,Cream', 22, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Traditional Palm Slippers', 'traditional-palm-slippers', 'Native-inspired palm slippers with a clean hand-finished look.', 12500, NULL, 'male', '40,41,42,43,44,45', 'Brown,Black', 16, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Rhinestone Party Slippers', 'rhinestone-party-slippers', 'Sparkly rhinestone slippers for parties, dinners, and events.', 11500, 14000, 'female', '36,37,38,39,40,41', 'Silver,Gold,Black', 12, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='slippers'), 'Minimal Strap Flip-Flops', 'minimal-strap-flip-flops', 'Lightweight flip-flops with a simple strap and flexible sole.', 4200, NULL, 'unisex', '37,38,39,40,41,42,43,44', 'Black,White,Navy', 45, 0, 0, 'active'),

-- Bags
((SELECT id FROM categories WHERE slug='bags'), 'Structured Tote Bag', 'structured-tote-bag', 'Spacious structured tote in premium faux leather for everyday essentials.', 18500, NULL, 'female', NULL, 'Black,Tan,Gold', 16, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Crossbody Sling Bag', 'crossbody-sling-bag', 'Compact crossbody sling that is practical and stylish for everyday carry.', 12500, 15000, 'unisex', NULL, 'Black,Brown', 22, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Mini Shoulder Bag', 'mini-shoulder-bag', 'Small shoulder bag with a sleek profile for outings and events.', 14500, NULL, 'female', NULL, 'Black,White,Red', 18, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Quilted Chain Bag', 'quilted-chain-bag', 'Quilted handbag with chain strap and polished hardware.', 21000, 25500, 'female', NULL, 'Black,Cream,Gold', 12, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Laptop Work Backpack', 'laptop-work-backpack', 'Organized backpack with laptop space and daily commute pockets.', 24000, NULL, 'unisex', NULL, 'Black,Grey,Navy', 14, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Drawstring Bucket Bag', 'drawstring-bucket-bag', 'Soft bucket bag with drawstring closure and adjustable strap.', 16500, 19500, 'female', NULL, 'Tan,Black,Olive', 15, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Travel Duffel Bag', 'travel-duffel-bag', 'Roomy duffel bag for weekend trips, gym days, and travel.', 27500, NULL, 'unisex', NULL, 'Black,Brown', 10, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Clutch Evening Purse', 'clutch-evening-purse', 'Elegant clutch purse with enough room for phone, cards, and lipstick.', 13000, NULL, 'female', NULL, 'Gold,Silver,Black', 20, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Canvas Market Tote', 'canvas-market-tote', 'Durable canvas tote for shopping, errands, and casual days.', 8500, 10000, 'unisex', NULL, 'Natural,Black,Green', 32, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='bags'), 'Top Handle Satchel', 'top-handle-satchel', 'Polished satchel with top handle, zip closure, and optional strap.', 22500, NULL, 'female', NULL, 'Black,Burgundy,Tan', 11, 1, 0, 'active'),

-- Sunglasses
((SELECT id FROM categories WHERE slug='sunglasses'), 'Classic Aviator Sunglasses', 'classic-aviator-sunglasses', 'Timeless aviator frames with UV-protective lenses.', 9500, NULL, 'unisex', NULL, 'Gold,Black,Silver', 30, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Oversized Square Sunglasses', 'oversized-square-sunglasses', 'Bold oversized square frames for a statement look.', 10500, 13000, 'female', NULL, 'Black,Tortoise', 18, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Retro Round Sunglasses', 'retro-round-sunglasses', 'Round retro frames with tinted lenses and a lightweight feel.', 9000, NULL, 'unisex', NULL, 'Gold,Black,Brown', 24, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Cat Eye Fashion Sunglasses', 'cat-eye-fashion-sunglasses', 'Sharp cat eye sunglasses with a chic fashion-forward profile.', 11000, 13500, 'female', NULL, 'Black,Red,Tortoise', 17, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Sport Shield Sunglasses', 'sport-shield-sunglasses', 'Wrap-style sport sunglasses with broad lens coverage.', 12000, NULL, 'unisex', NULL, 'Black,Blue,Silver', 21, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Transparent Frame Sunglasses', 'transparent-frame-sunglasses', 'Clear frame sunglasses with soft gradient lenses.', 9800, NULL, 'unisex', NULL, 'Clear,Pink,Cream', 19, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Wayfarer Everyday Sunglasses', 'wayfarer-everyday-sunglasses', 'Everyday wayfarer frames that work with almost any outfit.', 9200, 11000, 'unisex', NULL, 'Black,Brown', 28, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Rimless Gradient Sunglasses', 'rimless-gradient-sunglasses', 'Light rimless sunglasses with gradient lenses and slim arms.', 12500, NULL, 'female', NULL, 'Gold,Silver,Rose Gold', 14, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Luxury Metal Frame Sunglasses', 'luxury-metal-frame-sunglasses', 'Premium metal frame sunglasses with a refined polished finish.', 15000, 18000, 'unisex', NULL, 'Gold,Black', 12, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='sunglasses'), 'Kids Mini Sunglasses', 'kids-mini-sunglasses', 'Small colorful sunglasses for children and party gift styling.', 5500, NULL, 'unisex', NULL, 'Pink,Blue,Black', 26, 0, 0, 'active'),

-- Party Souvenirs
((SELECT id FROM categories WHERE slug='souvenirs'), 'Personalized Party Cups Set of 50', 'personalized-party-cups-set-of-50', 'Custom-printed party cups for weddings, birthdays, and celebrations.', 25000, NULL, 'unisex', NULL, NULL, 50, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Engraved Keychain Favors Set of 100', 'engraved-keychain-favors-set-of-100', 'Elegant engraved keychains as memorable takeaway gifts for guests.', 45000, NULL, 'unisex', NULL, NULL, 30, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Scented Candle Gift Set', 'scented-candle-gift-set', 'Beautifully packaged scented candles for thoughtful celebration gifts.', 6000, 7500, 'unisex', NULL, NULL, 60, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Custom Tote Bags Set of 25', 'custom-tote-bags-set-of-25', 'Reusable tote bags printed with names, dates, or event artwork.', 55000, NULL, 'unisex', NULL, NULL, 25, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Mini Perfume Bottles Set of 50', 'mini-perfume-bottles-set-of-50', 'Mini fragrance bottles packaged for luxury party souvenirs.', 70000, 85000, 'unisex', NULL, NULL, 20, 1, 1, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Personalized Notebooks Set of 30', 'personalized-notebooks-set-of-30', 'Custom notebooks with branded covers for showers, birthdays, and corporate events.', 42000, NULL, 'unisex', NULL, NULL, 35, 0, 0, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Acrylic Photo Frames Set of 40', 'acrylic-photo-frames-set-of-40', 'Clear acrylic frames that can be personalized for guest keepsakes.', 64000, NULL, 'unisex', NULL, NULL, 24, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Luxury Gift Boxes Set of 20', 'luxury-gift-boxes-set-of-20', 'Ready-to-fill gift boxes with ribbon detail for premium event favors.', 50000, 60000, 'unisex', NULL, NULL, 28, 1, 0, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Branded Hand Fans Set of 100', 'branded-hand-fans-set-of-100', 'Printed hand fans for outdoor parties, church events, and weddings.', 38000, NULL, 'unisex', NULL, NULL, 45, 0, 1, 'active'),
((SELECT id FROM categories WHERE slug='souvenirs'), 'Thank You Pouch Set of 50', 'thank-you-pouch-set-of-50', 'Small drawstring pouches ready for candies, jewelry, or mini gifts.', 30000, NULL, 'unisex', NULL, NULL, 55, 0, 0, 'active')
ON DUPLICATE KEY UPDATE slug = slug;

-- Online demo images. Existing image records are left untouched.
INSERT INTO product_images (product_id, image_url, is_primary, sort_order)
SELECT
  p.id,
  CASE c.slug
    WHEN 'jeans' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1602293589930-45aad59ba3ab?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1714729382668-7bc3bb261662?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1637069585336-827b298fe84a?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 't-shirts' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'jean-skirts' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1743356914615-66062f1850e6?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1601838536682-b2ed08d2b0aa?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'short-dresses' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1532579853048-ec5f8f15f88d?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1747396206869-75ea57b325ce?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1667890786022-98704b9b8fcb?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'jalabia' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1648329008114-bce0ec0b5950?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1762782777495-9d297f3d9d3d?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1780601247035-e34a7b06d35b?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'abayah' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1649109669757-d69d5c38c1b9?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1629200468327-78bdb7e47c85?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1780601247035-e34a7b06d35b?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'shoes' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1668069226492-508742b03147?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1519415943484-9fa1873496d4?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'slippers' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1603487742131-4160ec999306?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1585120824848-8a5cd41493d2?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1574791418059-2e8d961ab4fb?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'bags' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1598532163257-ae3c6b2524b6?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1600857062241-98e5dba7f214?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'sunglasses' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1584036553516-bf83210aa16c?auto=format&fit=crop&w=900&q=80'
    END
    WHEN 'souvenirs' THEN CASE img.sort_order
      WHEN 0 THEN 'https://images.unsplash.com/photo-1531956531700-dc0ee0f1f9a5?auto=format&fit=crop&w=900&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1765317269952-82ca1acdd2ca?auto=format&fit=crop&w=900&q=80'
      ELSE 'https://images.unsplash.com/photo-1768776182889-607915c299a6?auto=format&fit=crop&w=900&q=80'
    END
    ELSE 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=900&q=80'
  END,
  CASE WHEN img.sort_order = 0 THEN 1 ELSE 0 END,
  img.sort_order
FROM products p
JOIN categories c ON c.id = p.category_id
JOIN (
  SELECT 0 AS sort_order
  UNION ALL SELECT 1
  UNION ALL SELECT 2
) img
WHERE p.id NOT IN (SELECT product_id FROM product_images);
