<?php
/**
 * SEO & GEO specification data layer.
 *
 * Encodes the "Alugence — SEO & GEO Requirements" workbook (Tabs 2, 5,
 * 6, 14, 15, 16, 17) as structured data. Templates, meta output,
 * schema, navigation and Theme Setup all read from here — edit THIS
 * FILE to change titles, metas, H1s or architecture, not templates.
 *
 * Brand note: workbook was authored as "thinkfurther.ai"; all brand
 * strings are emitted as "Alugence" per rebrand instruction.
 *
 * Title tags <=60 chars, meta descriptions <=155 chars (validated by
 * the build script and covered by tf_spec_validate() in wp-admin).
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical entity paragraph (Tab 9, Layer 1).
 * Reused VERBATIM on Home, About and llms.txt. Keep identical to
 * LinkedIn/Crunchbase descriptions when those profiles are created.
 *
 * @return string
 */
function tf_entity_paragraph() {
	return __( 'Alugence is an AI consultancy based in Yorkshire, UK, specialising in two areas: AI business transformation and AI search (GEO/AEO) visibility. We help UK and US businesses adopt AI across their operations and get found, cited and recommended by AI engines such as ChatGPT, Gemini, Perplexity, Copilot and Google AI Overviews.', 'thinkfurther' );
}

/**
 * Full page specification, keyed by site path.
 *
 * Fields:
 * - h1        Exact H1 (one per page — Tab 5/14/15/17).
 * - title     Full <title> tag, <=60 chars (brand suffix included).
 * - meta      Meta description, <=155 chars.
 * - kw        Secondary/LSI keywords (informs the content stage).
 * - template  Rendering template key (service|pillar|local|sector|...).
 * - arm       A1 | A2 | Shared.
 * - parent    Parent path (page hierarchy + breadcrumbs).
 * - nav       primary | dropdown | footer | hub | none.
 * - nav_group Dropdown sub-header (Tab 14).
 * - priority  P1/P2/P3 (Tab 2).
 * - schema    Additional schema type key (Tab 8).
 * - usecases  Sector use-cases for H3s (Tab 15, industries only).
 * - guide     Related pillar guide path (service pages, Tab 7 links).
 *
 * @return array
 */
function tf_spec_pages() {
	static $pages = null;
	if ( null !== $pages ) {
		return $pages;
	}

	$pages = array(

		/* ---------------- Core & hubs (Tab 2 / Tab 5) ---------------- */

		'/' => array(
			'h1'       => 'AI consultancy and AI search optimisation for ambitious businesses',
			'title'    => 'AI Consultancy & AI Search Optimisation | Alugence',
			'meta'     => 'AI consultancy and AI search visibility experts. We help you adopt AI and get cited by ChatGPT, Gemini and Google AI Overviews. Book a call.',
			'kw'       => 'ai transformation, ai search visibility, geo, aeo, ai strategy',
			'template' => 'home',
			'arm'      => 'Shared',
			'nav'      => 'none',
			'priority' => 'P1',
		),

		'/ai-consultancy/' => array(
			'h1'       => 'AI consultancy that turns strategy into working systems',
			'title'    => 'AI Consultancy: Strategy to Delivery | Alugence',
			'meta'     => 'Independent AI consultancy for UK & US businesses: AI strategy, implementation, automation and custom development. Book a free discovery call.',
			'kw'       => 'ai consulting, ai consultant, artificial intelligence consulting, ai consulting services, ai consultancy uk',
			'template' => 'pillar',
			'arm'      => 'A1',
			'nav'      => 'primary',
			'priority' => 'P1',
		),

		'/ai-search/' => array(
			'h1'       => 'AI search consultancy: get found by AI, not just Google',
			'title'    => 'AI Search Consultancy: GEO, AEO & AI SEO | Alugence',
			'meta'     => 'AI search optimisation consultancy. Boost visibility in ChatGPT, Gemini, Perplexity & Google AI Overviews. Get your AI visibility audit.',
			'kw'       => 'ai search optimization, ai search visibility, seo for ai search, geo, aeo, ai search agency',
			'template' => 'pillar',
			'arm'      => 'A2',
			'nav'      => 'primary',
			'priority' => 'P1',
		),

		/* ---------------- Arm 1 services (Tabs 5 + 14) ---------------- */

		'/ai-consultancy/ai-strategy/' => array(
			'h1'        => 'AI strategy and transformation consulting',
			'title'     => 'AI Strategy Consulting & Transformation | Alugence',
			'meta'      => 'AI strategy consulting that builds a clear, costed AI roadmap. Assess readiness, prioritise use cases and lead transformation with confidence.',
			'kw'        => 'ai transformation, ai roadmap, ai readiness, ai strategy consultant, ai adoption',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Core services',
			'priority'  => 'P1',
			'guide'     => '/guides/ai-strategy-framework/',
		),
		'/ai-consultancy/ai-implementation/' => array(
			'h1'        => 'AI implementation and integration',
			'title'     => 'AI Implementation & Integration Services | Alugence',
			'meta'      => 'AI implementation and integration for enterprise systems. We deploy, integrate and scale AI safely into your existing stack. Talk to our team.',
			'kw'        => 'ai integration consulting, enterprise ai, ai deployment, ai systems integration',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Core services',
			'priority'  => 'P1',
		),
		'/ai-consultancy/ai-automation/' => array(
			'h1'        => 'AI automation and AI agents',
			'title'     => 'AI Automation & AI Agents for Business | Alugence',
			'meta'      => 'Automate real work with AI agents and intelligent workflow automation. Cut manual effort and scale operations. See how AI automation works.',
			'kw'        => 'ai agents, agentic ai, ai workflow automation, intelligent automation, business process automation',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Core services',
			'priority'  => 'P1',
			'guide'     => '/guides/what-is-agentic-ai/',
		),
		'/ai-consultancy/ai-development/' => array(
			'h1'        => 'Custom AI development and solutions',
			'title'     => 'Custom AI Development & Solutions | Alugence',
			'meta'      => 'Custom AI development, machine learning and bespoke AI solutions for business. From prototype to production. Build your AI solution with us.',
			'kw'        => 'ai software development, machine learning consulting, custom ai solutions, ai solutions for business',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Core services',
			'priority'  => 'P2',
		),
		'/ai-consultancy/ai-training/' => array(
			'h1'        => 'AI training and enablement',
			'title'     => 'AI Training & Enablement for Teams | Alugence',
			'meta'      => 'Practical AI training and enablement to upskill teams and drive adoption. Workshops, playbooks and hands-on support. Enquire about AI training.',
			'kw'        => 'ai upskilling, ai adoption, ai literacy, ai workshops',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Core services',
			'priority'  => 'P2',
		),
		'/ai-consultancy/fractional-ai-officer/' => array(
			'h1'        => 'Fractional Chief AI Officer (CAIO)',
			'title'     => 'Fractional Chief AI Officer (CAIO) | Alugence',
			'meta'      => 'Ongoing senior AI leadership without a full-time hire. Strategy, governance and delivery oversight from a fractional Chief AI Officer. Book a call.',
			'kw'        => 'fractional caio, fractional ai leadership',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Advisory & leadership',
			'priority'  => 'P1',
		),
		'/ai-consultancy/ai-readiness/' => array(
			'h1'        => 'AI readiness assessment',
			'title'     => 'AI Readiness Assessment | Alugence',
			'meta'      => 'Assess your data, tools, team and processes before you invest in AI. A structured AI readiness assessment that de-risks your first project.',
			'kw'        => 'ai readiness, ai maturity assessment',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Advisory & leadership',
			'priority'  => 'P1',
		),
		'/ai-consultancy/ai-governance/' => array(
			'h1'        => 'AI governance, risk and compliance',
			'title'     => 'AI Governance, Risk & Compliance | Alugence',
			'meta'      => 'AI governance, risk and compliance consulting. Deploy AI safely under the EU AI Act and UK rules with clear policies and controls. Talk to us.',
			'kw'        => 'ai governance framework, eu ai act, responsible ai',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Advisory & leadership',
			'priority'  => 'P1',
		),
		'/ai-consultancy/enterprise-ai/' => array(
			'h1'        => 'Enterprise AI consulting',
			'title'     => 'Enterprise AI Consulting | Alugence',
			'meta'      => 'Enterprise AI strategy, governance and delivery at scale. We work with your IT, security and compliance teams to roll out AI safely. Get in touch.',
			'kw'        => 'enterprise ai, ai at scale',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Advisory & leadership',
			'priority'  => 'P2',
		),
		'/ai-consultancy/ai-proof-of-concept/' => array(
			'h1'        => 'AI proof of concept and pilots',
			'title'     => 'AI Proof of Concept & Pilots | Alugence',
			'meta'      => 'Validate AI on your real data before you commit. Fast, fixed-scope AI proof of concept and pilots that prove value in weeks. Start your PoC.',
			'kw'        => 'ai pilot, ai poc',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Build & implementation',
			'priority'  => 'P2',
		),
		'/ai-consultancy/llm-deployment/' => array(
			'h1'        => 'LLM deployment: Claude and ChatGPT',
			'title'     => 'LLM Deployment: Claude & ChatGPT | Alugence',
			'meta'      => 'Roll out Claude, ChatGPT and custom GPTs across your teams. Licences, setup, guardrails and training in one managed LLM deployment. Book a call.',
			'kw'        => 'custom gpt, claude implementation, chatgpt for business',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Build & implementation',
			'priority'  => 'P1',
		),
		'/ai-consultancy/ai-chatbots/' => array(
			'h1'        => 'AI chatbots and conversational AI',
			'title'     => 'AI Chatbots & Conversational AI | Alugence',
			'meta'      => 'Custom AI chatbots and conversational AI that handle enquiries, bookings and support 24/7. Built on your data and tone of voice. See how.',
			'kw'        => 'ai chatbot development, conversational ai, ai chatbot for business',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Build & implementation',
			'priority'  => 'P2',
		),
		'/ai-consultancy/voice-ai/' => array(
			'h1'        => 'Voice AI agents for business',
			'title'     => 'Voice AI Agents for Business | Alugence',
			'meta'      => 'Voice AI agents that answer calls, qualify leads and book appointments automatically. Natural, on-brand and always available. Explore Voice AI.',
			'kw'        => 'ai voice agents, voice automation',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Build & implementation',
			'priority'  => 'P3',
		),
		'/ai-consultancy/mlops/' => array(
			'h1'        => 'MLOps and AI infrastructure',
			'title'     => 'MLOps & AI Infrastructure | Alugence',
			'meta'      => 'MLOps and AI infrastructure to deploy, monitor and scale models in production reliably. CI/CD for AI, observability and cost control. Talk to us.',
			'kw'        => 'ai infrastructure, model deployment',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Build & implementation',
			'priority'  => 'P3',
		),
		'/ai-consultancy/data-strategy/' => array(
			'h1'        => 'AI data strategy and governance',
			'title'     => 'AI Data Strategy & Governance | Alugence',
			'meta'      => 'Get your data ready for AI. Data strategy, governance, migration and structuring so your AI has clean, compliant, usable data. Book a review.',
			'kw'        => 'data governance, data migration, data structuring',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Data foundations',
			'priority'  => 'P2',
		),
		'/ai-consultancy/predictive-analytics/' => array(
			'h1'        => 'Predictive analytics and data science',
			'title'     => 'Predictive Analytics & Data Science | Alugence',
			'meta'      => 'Predictive analytics and machine learning that turn your data into forecasts and decisions. Data science consulting from prototype to production.',
			'kw'        => 'machine learning consulting, data science',
			'template'  => 'service',
			'arm'       => 'A1',
			'parent'    => '/ai-consultancy/',
			'nav'       => 'dropdown',
			'nav_group' => 'Data foundations',
			'priority'  => 'P3',
		),
		'/ai-consultancy/london/' => array(
			'h1'       => 'AI consultancy in London',
			'title'    => 'AI Consultancy London | Alugence',
			'meta'     => 'London AI consultancy helping businesses adopt AI with strategy, automation and delivery. Local expertise, national reach. Book a call.',
			'kw'       => 'ai consultants london, ai consulting london, london ai company',
			'template' => 'local',
			'arm'      => 'A1',
			'parent'   => '/ai-consultancy/',
			'nav'      => 'footer',
			'priority' => 'P2',
		),
		'/ai-consultancy/small-business/' => array(
			'h1'       => 'AI consulting for small businesses',
			'title'    => 'AI Consulting for Small Business | Alugence',
			'meta'     => 'Affordable AI consulting for small businesses. Practical, no-jargon help to adopt AI, automate tasks and compete. Book a free consultation.',
			'kw'       => 'ai for small business, small business automation, affordable ai consulting',
			'template' => 'service',
			'arm'      => 'A1',
			'parent'   => '/ai-consultancy/',
			'nav'      => 'hub',
			'priority' => 'P2',
		),

		/* ---------------- Arm 2 services (Tab 5) ---------------- */

		'/ai-search/generative-engine-optimisation/' => array(
			'h1'        => 'Generative Engine Optimisation (GEO)',
			'title'     => 'Generative Engine Optimisation (GEO) | Alugence',
			'meta'      => 'GEO services that make your brand the answer in generative AI search. Get cited by ChatGPT, Gemini and Perplexity. Request a GEO audit.',
			'kw'        => 'geo services, geo agency, generative engine optimization, ai citations',
			'template'  => 'service',
			'arm'       => 'A2',
			'parent'    => '/ai-search/',
			'nav'       => 'dropdown',
			'priority'  => 'P1',
			'guide'     => '/guides/what-is-geo/',
		),
		'/ai-search/answer-engine-optimisation/' => array(
			'h1'        => 'Answer Engine Optimisation (AEO)',
			'title'     => 'Answer Engine Optimisation (AEO) | Alugence',
			'meta'      => 'AEO services that win answer-engine and AI Overview placements. Structure content so AI engines quote you first. Request an AEO audit.',
			'kw'        => 'aeo services, aeo agency, answer engine optimization, ai overviews',
			'template'  => 'service',
			'arm'       => 'A2',
			'parent'    => '/ai-search/',
			'nav'       => 'dropdown',
			'priority'  => 'P1',
			'guide'     => '/guides/what-is-aeo/',
		),
		'/ai-search/ai-search-visibility/' => array(
			'h1'        => 'AI search visibility tracking and audits',
			'title'     => 'AI Search Visibility Tracking & Audits | Alugence',
			'meta'      => 'Measure and grow your AI search visibility. Track brand mentions and citations across ChatGPT, Gemini, Perplexity and Copilot. Book an audit.',
			'kw'        => 'ai brand visibility, ai visibility tracking, ai search visibility tool, share of voice ai',
			'template'  => 'service',
			'arm'       => 'A2',
			'parent'    => '/ai-search/',
			'nav'       => 'dropdown',
			'priority'  => 'P1',
			'guide'     => '/guides/improve-brand-visibility-ai-search/',
		),
		'/ai-search/llm-optimisation/' => array(
			'h1'        => 'LLM and ChatGPT optimisation',
			'title'     => 'LLM & ChatGPT Optimisation Services | Alugence',
			'meta'      => 'Optimise your website to be cited by ChatGPT, Perplexity, Gemini and Copilot. LLM optimisation that turns AI answers into traffic and leads.',
			'kw'        => 'optimize for chatgpt, perplexity optimisation, get cited by ai, chatgpt seo',
			'template'  => 'service',
			'arm'       => 'A2',
			'parent'    => '/ai-search/',
			'nav'       => 'dropdown',
			'priority'  => 'P1',
			'guide'     => '/guides/get-cited-by-ai/',
		),
		'/ai-search/ai-seo/' => array(
			'h1'        => 'AI SEO: SEO for the age of AI search',
			'title'     => 'AI SEO Services & Consultancy | Alugence',
			'meta'      => 'AI SEO consultancy bridging classic SEO and AI search. Rank in Google and get cited by AI engines. Work with an AI SEO consultant.',
			'kw'        => 'ai seo consultant, ai seo consultancy, seo for ai search, seo for ai search engines',
			'template'  => 'service',
			'arm'       => 'A2',
			'parent'    => '/ai-search/',
			'nav'       => 'dropdown',
			'priority'  => 'P1',
		),
		'/ai-search/ai-seo-london/' => array(
			'h1'       => 'AI SEO consultancy in London',
			'title'    => 'AI SEO Consultancy London | Alugence',
			'meta'     => 'London AI SEO consultancy. Improve rankings and AI search visibility across ChatGPT, Gemini and Google AI Overviews. Book a consultation.',
			'kw'       => 'ai seo consultant london, ai seo agency london',
			'template' => 'local',
			'arm'      => 'A2',
			'parent'   => '/ai-search/',
			'nav'      => 'footer',
			'priority' => 'P2',
		),

		/* ---------------- Proof & industries (Tabs 2 + 15) ---------------- */

		'/case-studies/' => array(
			'h1'       => 'Case studies: measurable AI and AI search results',
			'title'    => 'AI Consultancy Case Studies & Results | Alugence',
			'meta'     => 'Real results from our AI consultancy and AI search work. See how we help businesses adopt AI and win AI search visibility. Explore case studies.',
			'kw'       => 'ai case studies, geo case study, ai transformation results',
			'template' => 'cpt-archive',
			'arm'      => 'Shared',
			'nav'      => 'primary',
			'priority' => 'P1',
		),
		'/industries/' => array(
			'h1'       => 'Industries: AI built for your sector',
			'title'    => 'AI Solutions by Industry | Alugence',
			'meta'     => 'AI consulting tailored to your sector — financial services, healthcare, legal, retail, manufacturing and more. See sector-specific use cases.',
			'kw'       => 'ai by industry, sector ai, vertical ai',
			'template' => 'industries-index',
			'arm'      => 'Shared',
			'nav'      => 'primary',
			'priority' => 'P1',
		),
		'/industries/financial-services/' => array(
			'h1'       => 'AI for financial services',
			'title'    => 'AI for Financial Services | Alugence',
			'meta'     => 'AI consulting for financial services: automate underwriting, compliance checks and client onboarding safely and compliantly. Book a discovery call.',
			'kw'       => 'ai in financial services, ai in finance',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P1',
			'usecases' => array( 'Underwriting automation', 'AML / compliance checks', 'Onboarding & KYC', 'Fraud detection', 'Client reporting' ),
		),
		'/industries/healthcare-dental/' => array(
			'h1'       => 'AI for healthcare and dental',
			'title'    => 'AI for Healthcare & Dental | Alugence',
			'meta'     => 'AI for healthcare and dental practices: cut no-shows, automate patient comms and streamline clinical admin. Compliant, practical AI. Talk to us.',
			'kw'       => 'ai in healthcare',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P1',
			'usecases' => array( 'No-show reduction', 'Patient comms & reminders', 'Clinical admin', 'Triage assistants', 'Records summarisation' ),
		),
		'/industries/professional-services/' => array(
			'h1'       => 'AI for professional services',
			'title'    => 'AI for Professional Services | Alugence',
			'meta'     => 'AI for professional services firms: automate document drafting, research and admin so senior staff focus on higher-value work. See how.',
			'kw'       => 'ai for professional services, ai for accountants',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P1',
			'usecases' => array( 'Document drafting', 'Research automation', 'Proposal generation', 'Time capture', 'Knowledge search' ),
		),
		'/industries/legal/' => array(
			'h1'       => 'AI for legal and law firms',
			'title'    => 'AI for Legal & Law Firms | Alugence',
			'meta'     => 'AI for law firms: speed up contract review, legal research and drafting with secure, compliant AI built for legal workflows. Book a consultation.',
			'kw'       => 'ai for law firms, legal ai',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P2',
			'usecases' => array( 'Contract review', 'Legal research', 'Drafting', 'Due diligence', 'Matter summarisation' ),
		),
		'/industries/retail-ecommerce/' => array(
			'h1'       => 'AI for retail and e-commerce',
			'title'    => 'AI for Retail & E-commerce | Alugence',
			'meta'     => 'AI for retail and e-commerce: personalisation, demand forecasting and customer analytics that lift revenue and margin. Explore retail AI.',
			'kw'       => 'ai in retail',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P2',
			'usecases' => array( 'Personalisation & recommendations', 'Demand forecasting', 'Customer analytics', 'Pricing', 'Support automation' ),
		),
		'/industries/real-estate/' => array(
			'h1'       => 'AI for real estate and property',
			'title'    => 'AI for Real Estate & Property | Alugence',
			'meta'     => 'AI for real estate and property: automate valuations, listings and lead qualification with AI that speaks to buyers 24/7. Get started.',
			'kw'       => 'ai in real estate',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P2',
			'usecases' => array( 'Automated valuations', 'Listing generation', 'Lead qualification', 'Viewing bookings', 'Document processing' ),
		),
		'/industries/logistics-transport/' => array(
			'h1'       => 'AI for logistics and transport',
			'title'    => 'AI for Logistics & Transport | Alugence',
			'meta'     => 'AI for logistics and transport: optimise route planning, automate dispatch and improve fleet utilisation with practical AI. Talk to our team.',
			'kw'       => 'ai in logistics',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P2',
			'usecases' => array( 'Route optimisation', 'Dispatch automation', 'Fleet utilisation', 'Demand forecasting', 'ETA prediction' ),
		),
		'/industries/construction/' => array(
			'h1'       => 'AI for construction and engineering',
			'title'    => 'AI for Construction & Engineering | Alugence',
			'meta'     => 'AI for construction and engineering: faster estimating, document processing and risk analysis with AI built for the sector. Book a call.',
			'kw'       => 'ai in construction',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P3',
			'usecases' => array( 'Estimating', 'Document & drawing processing', 'Risk analysis', 'H&S compliance', 'RFI automation' ),
		),
		'/industries/manufacturing/' => array(
			'h1'       => 'AI for manufacturing',
			'title'    => 'AI for Manufacturing | Alugence',
			'meta'     => 'AI for manufacturing: predictive maintenance, quality assurance and process optimisation that cut downtime and waste. See manufacturing AI.',
			'kw'       => 'ai in manufacturing, ai for manufacturing',
			'template' => 'sector',
			'arm'      => 'A1',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P2',
			'usecases' => array( 'Predictive maintenance', 'Quality assurance & vision', 'Process optimisation', 'Demand planning', 'Supply chain' ),
		),
		'/industries/marketing-agencies/' => array(
			'h1'       => 'AI for marketing and agencies',
			'title'    => 'AI for Marketing & Agencies | Alugence',
			'meta'     => 'AI for marketing teams and agencies: automate content and reporting AND win visibility in AI search (GEO/AEO). One partner for both. Talk to us.',
			'kw'       => 'ai for marketing',
			'template' => 'sector',
			'arm'      => 'Shared',
			'parent'   => '/industries/',
			'nav'      => 'dropdown',
			'priority' => 'P2',
			'usecases' => array( 'Content automation', 'Reporting', 'GEO/AEO for clients', 'AI search visibility', 'Campaign analytics' ),
		),

		/* ---------------- Trust & conversion (Tab 2) ---------------- */

		'/about/' => array(
			'h1'       => 'About Alugence',
			'title'    => 'About Alugence | AI & AI Search Consultants',
			'meta'     => 'Alugence is an AI consultancy specialising in AI transformation and AI search visibility. Meet the team, our approach and our credentials.',
			'kw'       => 'ai consultancy team, ai experts, ai search experts',
			'template' => 'about',
			'arm'      => 'Shared',
			'nav'      => 'primary',
			'priority' => 'P1',
			'schema'   => 'AboutPage',
		),
		'/about/team/' => array(
			'h1'       => 'Our team: named consultants, real credentials',
			'title'    => 'Meet the Team | Alugence AI Consultants',
			'meta'     => 'Meet the named consultants behind Alugence — AI strategy, engineering and AI search experts helping UK & US businesses adopt and win with AI.',
			'kw'       => 'ai consultants, ai search consultants',
			'template' => 'team',
			'arm'      => 'Shared',
			'parent'   => '/about/',
			'nav'      => 'hub',
			'priority' => 'P1',
		),
		'/careers/' => array(
			'h1'       => 'Careers at Alugence',
			'title'    => 'Careers | Alugence',
			'meta'     => 'Join Alugence — an AI consultancy working across AI transformation and AI search visibility for UK and US clients. See open roles.',
			'kw'       => 'ai consultancy jobs',
			'template' => 'careers',
			'arm'      => 'Shared',
			'nav'      => 'footer',
			'priority' => 'P3',
		),
		'/contact/' => array(
			'h1'       => 'Contact Alugence',
			'title'    => 'Contact Alugence | Book a Discovery Call',
			'meta'     => 'Talk to Alugence about AI consultancy or AI search optimisation. Book a free discovery call or send a message. UK and US clients welcome.',
			'kw'       => 'book ai consultation, ai consultancy contact',
			'template' => 'contact',
			'arm'      => 'Shared',
			'nav'      => 'cta',
			'priority' => 'P1',
			'schema'   => 'ContactPage',
		),
		'/pricing/' => array(
			'h1'       => 'AI consultancy pricing and engagement models',
			'title'    => 'AI Consultancy Pricing & Packages | Alugence',
			'meta'     => 'Transparent pricing for AI consultancy and AI search services. Fixed-fee packages, retainers and day rates for UK & US businesses. See our pricing.',
			'kw'       => 'ai consulting cost, ai consulting prices, how much does ai consulting cost, geo pricing',
			'template' => 'pricing',
			'arm'      => 'Shared',
			'nav'      => 'primary',
			'priority' => 'P1',
		),

		/* ---------------- Resources (Tab 2) ---------------- */

		'/resources/' => array(
			'h1'       => 'Resources: AI and AI search guides',
			'title'    => 'AI & AI Search Resources & Guides | Alugence',
			'meta'     => 'Guides, glossary and insights on AI transformation, GEO, AEO and AI search visibility. Learn how to adopt AI and get cited by AI engines.',
			'kw'       => 'geo guide, aeo guide, ai glossary, ai search insights',
			'template' => 'resources',
			'arm'      => 'Shared',
			'nav'      => 'primary',
			'priority' => 'P1',
		),
		'/guides/' => array(
			'h1'       => 'Guides: definitive answers on AI and AI search',
			'title'    => 'AI & AI Search Guides | Alugence',
			'meta'     => 'In-depth guides on GEO, AEO, AI search visibility, agentic AI and AI transformation. Learn the fundamentals and how to apply them.',
			'kw'       => 'geo guide, aeo guide, agentic ai guide',
			'template' => 'cpt-archive',
			'arm'      => 'Shared',
			'parent'   => '/resources/',
			'nav'      => 'hub',
			'priority' => 'P1',
		),
		'/blog/' => array(
			'h1'       => 'Blog: AI and AI search insights',
			'title'    => 'AI & AI Search Blog | Alugence',
			'meta'     => 'Practical articles on AI adoption, automation, AI search visibility and the best GEO/AEO tools. Insights for UK & US business leaders.',
			'kw'       => 'ai blog, geo blog, aeo insights',
			'template' => 'blog-index',
			'arm'      => 'Shared',
			'parent'   => '/resources/',
			'nav'      => 'hub',
			'priority' => 'P1',
		),
		'/glossary/' => array(
			'h1'       => 'Glossary: AI and AI search terms defined',
			'title'    => 'AI & AI Search Glossary | Alugence',
			'meta'     => 'Plain-English definitions of AI and AI search terms — GEO, AEO, AI search visibility, agentic AI, LLMs and more. One clear definition each.',
			'kw'       => 'geo definition, aeo definition, ai glossary',
			'template' => 'cpt-archive',
			'arm'      => 'Shared',
			'parent'   => '/resources/',
			'nav'      => 'footer',
			'priority' => 'P2',
		),
		'/tools/' => array(
			'h1'       => 'Free tools: measure your AI opportunity',
			'title'    => 'Free AI Tools | Alugence',
			'meta'     => 'Free tools from Alugence: estimate your AI ROI and readiness, and check your brand\'s visibility across ChatGPT, Gemini and Perplexity.',
			'kw'       => 'ai roi calculator, ai readiness audit, ai visibility checker',
			'template' => 'tools',
			'arm'      => 'Shared',
			'nav'      => 'footer',
			'priority' => 'P2',
		),
		'/tools/ai-roi-calculator/' => array(
			'h1'       => 'AI ROI calculator and opportunity report',
			'title'    => 'AI ROI Calculator | Estimate AI Savings | Alugence',
			'meta'     => 'Free AI ROI calculator. Estimate the hours and money AI could save your business, plus your AI readiness score. Get a branded AI Opportunity Report.',
			'kw'       => 'ai savings calculator, ai cost calculator, ai roi',
			'template' => 'roi-calculator',
			'arm'      => 'A1',
			'parent'   => '/tools/',
			'nav'      => 'hub',
			'priority' => 'P1',
			'schema'   => 'WebApplication',
		),

		/* ---------------- Legal (Tab 2) ---------------- */

		'/privacy-policy/' => array(
			'h1'       => 'Privacy policy',
			'title'    => 'Privacy Policy | Alugence',
			'meta'     => 'How Alugence collects, uses and protects your personal data, including form submissions and analytics. GDPR-compliant privacy policy.',
			'kw'       => '',
			'template' => 'legal',
			'arm'      => 'Shared',
			'nav'      => 'footer',
			'priority' => 'P1',
		),
		'/terms/' => array(
			'h1'       => 'Terms and conditions',
			'title'    => 'Terms & Conditions | Alugence',
			'meta'     => 'The terms and conditions governing use of the Alugence website and engagement of our AI consultancy and AI search services.',
			'kw'       => '',
			'template' => 'legal',
			'arm'      => 'Shared',
			'nav'      => 'footer',
			'priority' => 'P2',
		),
		'/cookie-policy/' => array(
			'h1'       => 'Cookie policy',
			'title'    => 'Cookie Policy | Alugence',
			'meta'     => 'The cookies the Alugence website uses, what they do, and how to control them. Analytics and preference cookies explained.',
			'kw'       => '',
			'template' => 'legal',
			'arm'      => 'Shared',
			'nav'      => 'footer',
			'priority' => 'P2',
		),
	);

	return $pages;
}

/**
 * Pillar guide seeds (Tabs 4, 5, 11) — created as DRAFT tf_guide posts
 * by Theme Setup so the content stage fills them in. Titles without
 * brand suffix per Tab 5 note ("Guides may omit brand suffix to fit").
 *
 * @return array
 */
function tf_spec_guides() {
	return array(
		'what-is-geo' => array(
			'h1'    => 'What is Generative Engine Optimisation (GEO)?',
			'title' => 'What is Generative Engine Optimisation (GEO)?',
			'meta'  => 'Generative Engine Optimisation (GEO) is how brands get cited in AI-generated answers. Learn what GEO is, how it works and how to do it.',
			'kw'    => 'geo meaning, generative engine optimization, how geo works, geo vs seo',
			'money' => '/ai-search/generative-engine-optimisation/',
			'money_anchor' => 'our GEO services',
			'priority' => 'P1',
		),
		'what-is-aeo' => array(
			'h1'    => 'What is Answer Engine Optimisation (AEO)?',
			'title' => 'What is Answer Engine Optimisation (AEO)?',
			'meta'  => 'Answer Engine Optimisation (AEO) helps your content become the answer AI engines give. Learn what AEO is, why it matters and how to start.',
			'kw'    => 'aeo meaning, answer engine optimization, optimise for answer engines',
			'money' => '/ai-search/answer-engine-optimisation/',
			'money_anchor' => 'our AEO services',
			'priority' => 'P1',
		),
		'aeo-vs-geo' => array(
			'h1'    => "AEO vs GEO vs SEO: what's the difference?",
			'title' => "AEO vs GEO vs SEO: What's the Difference?",
			'meta'  => 'AEO, GEO and SEO explained and compared. Understand how answer, generative and search engine optimisation differ, and when to use each.',
			'kw'    => 'geo vs aeo, aeo vs seo, difference between geo and seo',
			'money' => '/ai-search/',
			'money_anchor' => 'AI search consultancy',
			'priority' => 'P1',
		),
		'improve-brand-visibility-ai-search' => array(
			'h1'    => 'How to improve brand visibility in AI search',
			'title' => 'How to Improve Brand Visibility in AI Search',
			'meta'  => "A practical guide to improving your brand's visibility in AI search engines like ChatGPT, Gemini and Perplexity. 10 strategies that work.",
			'kw'    => 'ai search visibility strategies, brand mentions in ai, ai citations',
			'money' => '/ai-search/ai-search-visibility/',
			'money_anchor' => 'AI search visibility audit',
			'priority' => 'P1',
		),
		'get-cited-by-ai' => array(
			'h1'    => 'How to get cited by AI search engines',
			'title' => 'How to Get Cited by AI Search Engines',
			'meta'  => 'How to get your brand cited by ChatGPT, Gemini and Perplexity: sources, structure and signals that make AI engines quote you. Step-by-step.',
			'kw'    => 'ai citations, get quoted by ai, llm citations',
			'money' => '/ai-search/llm-optimisation/',
			'money_anchor' => 'LLM optimisation services',
			'priority' => 'P1',
		),
		'what-is-agentic-ai' => array(
			'h1'    => 'What is agentic AI?',
			'title' => 'What is Agentic AI? Definition, Examples & Uses',
			'meta'  => 'Agentic AI explained: what agentic AI is, how AI agents work, real examples and business use cases. A clear guide for decision-makers.',
			'kw'    => 'ai agents, agentic ai meaning, ai agent examples, autonomous ai',
			'money' => '/ai-consultancy/ai-automation/',
			'money_anchor' => 'AI automation services',
			'priority' => 'P1',
		),
	);
}

/**
 * Glossary seeds (Tab 9 Layer 1: "entities you want to own").
 * Created as DRAFT tf_glossary posts by Theme Setup.
 *
 * @return array
 */
function tf_spec_glossary() {
	return array(
		'generative-engine-optimisation' => array(
			'term'  => 'Generative Engine Optimisation (GEO)',
			'guide' => '/guides/what-is-geo/',
		),
		'answer-engine-optimisation' => array(
			'term'  => 'Answer Engine Optimisation (AEO)',
			'guide' => '/guides/what-is-aeo/',
		),
		'ai-search-visibility' => array(
			'term'  => 'AI search visibility',
			'guide' => '/guides/improve-brand-visibility-ai-search/',
		),
		'agentic-ai' => array(
			'term'  => 'Agentic AI',
			'guide' => '/guides/what-is-agentic-ai/',
		),
		'llms-txt' => array(
			'term'  => 'llms.txt',
			'guide' => '',
		),
	);
}

/**
 * Pricing tiers (Tab 17). Figures are INDICATIVE benchmarks —
 * ACTION FOR GRAHAM: confirm/replace with real rates before launch.
 * "From" format is deliberate so numbers can change without breaking
 * layout. Prices ex-VAT.
 *
 * @return array
 */
function tf_spec_pricing() {
	return array(
		'a1' => array(
			'heading' => __( 'AI consultancy pricing', 'thinkfurther' ),
			'tiers'   => array(
				array(
					'name'     => 'AI Readiness Sprint',
					'gbp'      => 'From £3,500',
					'usd'      => 'From $4,500',
					'duration' => '2 weeks',
					'includes' => array( 'Readiness score & diagnostic', 'Data & process audit', 'Regulatory perimeter check', '3–5 prioritised use cases (value/effort/risk)', 'Written report' ),
					'best'     => 'First-time buyers wanting a low-risk start',
					'link'     => '/ai-consultancy/ai-readiness/',
				),
				array(
					'name'     => 'AI Discovery & Pilot',
					'gbp'      => 'From £15,000',
					'usd'      => 'From $19,000',
					'duration' => '4–8 weeks',
					'includes' => array( 'Working prototype/PoC on your real data', 'Solution design', 'Deployment plan', 'Named senior consultant lead', 'Technical documentation' ),
					'best'     => 'Validating a use case before scaling',
					'link'     => '/ai-consultancy/ai-proof-of-concept/',
				),
				array(
					'name'     => 'AI Build & Embed',
					'gbp'      => 'From £40,000',
					'usd'      => 'From $50,000',
					'duration' => '8–16 weeks',
					'includes' => array( 'Production deployment', 'Integration with existing systems', 'GDPR & security posture', 'Staff onboarding + training', 'Runbook & monitoring handover' ),
					'best'     => 'Rolling AI into live operations',
					'link'     => '/ai-consultancy/ai-implementation/',
				),
				array(
					'name'     => 'Fractional Chief AI Officer',
					'gbp'      => 'From £3,000/mo',
					'usd'      => 'From $3,800/mo',
					'duration' => 'Ongoing (rolling)',
					'includes' => array( 'Senior AI leadership', 'Strategy, governance & vendor selection', 'Roadmap ownership', 'Board reporting', 'Typically 2–4 days/month' ),
					'best'     => 'SMEs/mid-market needing AI leadership',
					'link'     => '/ai-consultancy/fractional-ai-officer/',
				),
				array(
					'name'     => 'Day rate',
					'gbp'      => '£950–1,500',
					'usd'      => '$1,200–1,900',
					'duration' => 'As needed',
					'includes' => array( 'Advisory', 'Workshops', 'Delivery where a fixed fee is not practical' ),
					'best'     => 'Ad-hoc or scoped support',
					'link'     => '/ai-consultancy/',
				),
			),
		),
		'a2' => array(
			'heading' => __( 'AI search pricing', 'thinkfurther' ),
			'tiers'   => array(
				array(
					'name'     => 'AI Visibility Audit',
					'gbp'      => 'From £2,500',
					'usd'      => 'From $3,200',
					'duration' => '2–3 weeks',
					'includes' => array( 'Baseline visibility across ChatGPT, Gemini, Perplexity & AI Overviews', 'GEO/AEO gap analysis', 'Prioritised action plan', 'Competitor benchmark' ),
					'best'     => 'Understanding where you stand in AI search',
					'link'     => '/ai-search/ai-search-visibility/',
				),
				array(
					'name'     => 'GEO/AEO Sprint',
					'gbp'      => 'From £7,500',
					'usd'      => 'From $9,500',
					'duration' => '4–6 weeks',
					'includes' => array( 'Priority GEO/AEO fixes', 'Entity & schema work', 'Content restructure', 'llms.txt', 'FAQ + answer-first rewrites for key pages' ),
					'best'     => 'A focused push to start earning AI citations',
					'link'     => '/ai-search/generative-engine-optimisation/',
				),
				array(
					'name'     => 'AI Search Retainer',
					'gbp'      => 'From £3,000/mo',
					'usd'      => 'From $3,800/mo',
					'duration' => 'Ongoing (3-mo min)',
					'includes' => array( 'Continuous visibility tracking', 'Content optimisation', 'Citation building', 'Monthly AI share-of-voice reporting' ),
					'best'     => 'Sustained growth in AI search visibility',
					'link'     => '/ai-search/ai-search-visibility/',
				),
				array(
					'name'     => 'Enterprise / bespoke',
					'gbp'      => 'POA',
					'usd'      => 'POA',
					'duration' => 'Scoped',
					'includes' => array( 'Multi-brand or large-catalogue programmes', 'Custom tracking', 'Team training' ),
					'best'     => 'Larger orgs / agencies',
					'link'     => '/contact/',
				),
			),
		),
	);
}

/**
 * Look up the spec record for the current request path, if any.
 *
 * @param string $path Optional site-relative path (defaults to current).
 * @return array|null
 */
function tf_spec_for_path( $path = '' ) {
	if ( ! $path ) {
		if ( is_front_page() ) {
			$path = '/';
		} elseif ( is_post_type_archive() ) {
			$url  = get_post_type_archive_link( get_query_var( 'post_type' ) );
			$path = $url ? wp_parse_url( $url, PHP_URL_PATH ) : '';
		} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
			$url  = get_permalink( get_option( 'page_for_posts' ) );
			$path = $url ? wp_parse_url( $url, PHP_URL_PATH ) : '';
		} else {
			$url  = get_permalink( get_queried_object_id() );
			$path = $url ? wp_parse_url( $url, PHP_URL_PATH ) : '';
		}
	}
	if ( ! $path ) {
		return null;
	}
	$path  = trailingslashit( '/' . ltrim( $path, '/' ) );
	$pages = tf_spec_pages();
	return isset( $pages[ $path ] ) ? $pages[ $path ] : null;
}

/**
 * Children of a given spec path (for pillar hubs, industries index, nav).
 *
 * @param string $parent Parent path.
 * @return array path => record
 */
function tf_spec_children( $parent ) {
	$out = array();
	foreach ( tf_spec_pages() as $path => $rec ) {
		if ( isset( $rec['parent'] ) && $rec['parent'] === $parent ) {
			$out[ $path ] = $rec;
		}
	}
	return $out;
}

/**
 * Clearly-labelled placeholder copy block (structural build stage).
 * The content stage replaces these via the page editor — placeholder
 * only renders when the WP editor content for the page is empty.
 *
 * @param string $label Section label.
 * @param string $brief One-line brief for the content agent.
 * @param int    $words Approximate word count for the final copy.
 */
function tf_placeholder( $label, $brief, $words = 120 ) {
	printf(
		'<div class="tf-placeholder"><p class="tf-placeholder__tag">%s</p><p>%s</p></div>',
		esc_html( sprintf( '[PLACEHOLDER — %s · ~%d words]', $label, $words ) ),
		esc_html( $brief )
	);
}
