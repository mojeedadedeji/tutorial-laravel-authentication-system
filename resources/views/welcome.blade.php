<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>5050 Logistics Limited — Freight &amp; Supply Chain Solutions</title>
    <meta name="description" content="5050 Logistics Limited — dependable freight, warehousing and last-mile delivery across Nigeria and beyond.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --navy: #0b1f3a;
            --navy-2: #12294d;
            --blue: #2563eb;
            --blue-dark: #1d4ed8;
            --accent: #f59e0b;
        }
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: #1f2937; }
        .navbar-custom { background: rgba(11, 31, 58, 0.97); padding: 0.875rem 0; }
        .navbar-custom .navbar-brand img { height: 2.5rem; }
        .nav-link-custom { color: #cbd5e1 !important; font-weight: 500; font-size: 0.9375rem; }
        .nav-link-custom:hover { color: #fff !important; }
        .btn-quote { background: var(--accent); border-color: var(--accent); color: #111827; font-weight: 600; }
        .btn-quote:hover { background: #d97706; border-color: #d97706; color: #fff; }
        .btn-outline-light-custom { color: #fff; border-color: rgba(255,255,255,0.4); }
        .btn-outline-light-custom:hover { background: rgba(255,255,255,0.1); color: #fff; border-color: #fff; }

        .hero {
            background:
                radial-gradient(1200px 500px at 80% -10%, rgba(37,99,235,0.35), transparent 60%),
                linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 60%, #1e3a8a 100%);
            color: #fff;
            padding: 6rem 0 5rem;
            position: relative;
            overflow: hidden;
        }
        .hero { position: relative; overflow: hidden; }
        .hero-img-wrap { position: relative; }
        .hero-img {
            width: 100%;
            border-radius: 1.25rem;
            box-shadow: 0 24px 60px rgb(0 0 0 / 0.35);
            border: 1px solid rgb(255 255 255 / 0.15);
        }
        .hero-img-badge {
            position: absolute;
            bottom: -1.25rem; left: -1.25rem;
            background: #fff; color: var(--navy);
            border-radius: 0.875rem;
            padding: 0.875rem 1.25rem;
            box-shadow: 0 12px 32px rgb(0 0 0 / 0.2);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .hero-img-badge .pulse {
            width: 0.625rem; height: 0.625rem; border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 0 rgba(22,163,74,0.5);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(22,163,74,0.5); }
            70% { box-shadow: 0 0 0 10px rgba(22,163,74,0); }
            100% { box-shadow: 0 0 0 0 rgba(22,163,74,0); }
        }
        .fleet-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
        .fleet-item { position: relative; border-radius: 1rem; overflow: hidden; aspect-ratio: 4 / 3; }
        .fleet-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .fleet-item:hover img { transform: scale(1.06); }
        .fleet-item .fleet-label {
            position: absolute; inset: auto 0 0 0;
            padding: 2.5rem 1rem 0.875rem;
            background: linear-gradient(transparent, rgb(11 31 58 / 0.85));
            color: #fff; font-weight: 600; font-size: 0.875rem;
        }
        @media (max-width: 991.98px) {
            .fleet-grid { grid-template-columns: repeat(2, 1fr); }
            .hero-img-badge { left: 0; }
        }
        @media (max-width: 575.98px) {
            .fleet-grid { grid-template-columns: 1fr; }
        }
        .hero::after {
            content: "";
            position: absolute; inset: auto 0 0 0; height: 6rem;
            background: linear-gradient(transparent, #f8fafc);
        }
        .hero h1 { font-size: clamp(2.25rem, 5vw, 3.5rem); font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
        .hero .lead { color: #cbd5e1; font-size: 1.125rem; max-width: 34rem; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 999px;
            padding: 0.375rem 1rem;
            font-size: 0.8125rem; font-weight: 600; color: #e2e8f0;
            margin-bottom: 1.5rem;
        }
        .hero-stats { display: flex; gap: 2.5rem; flex-wrap: wrap; margin-top: 3rem; }
        .hero-stat .num { font-size: 2rem; font-weight: 800; color: #fff; }
        .hero-stat .lbl { font-size: 0.8125rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }

        .section { padding: 5rem 0; }
        .section-title { font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; color: var(--navy); }
        .section-sub { color: #64748b; max-width: 36rem; }

        .service-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.06), 0 8px 24px rgb(0 0 0 / 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgb(0 0 0 / 0.12); }
        .service-icon {
            width: 3.25rem; height: 3.25rem;
            border-radius: 0.875rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.375rem;
            margin-bottom: 1rem;
        }

        .step-num {
            width: 2.5rem; height: 2.5rem; border-radius: 50%;
            background: var(--blue); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; margin-bottom: 1rem;
        }

        .cta-band {
            background: linear-gradient(120deg, var(--navy), #1e3a8a);
            color: #fff;
            border-radius: 1.5rem;
            padding: 3.5rem 2rem;
            text-align: center;
        }
        .cta-band h2 { font-weight: 800; letter-spacing: -0.02em; }
        .cta-band p { color: #cbd5e1; }

        footer { background: var(--navy); color: #94a3b8; padding: 3rem 0 2rem; font-size: 0.875rem; }
        footer a { color: #cbd5e1; text-decoration: none; }
        footer a:hover { color: #fff; }
        footer h6 { color: #fff; font-weight: 700; margin-bottom: 1rem; }
        footer .footer-logo { height: 2.25rem; margin-bottom: 1rem; }

        @media (max-width: 991.98px) {
            .hero { padding: 4rem 0 3.5rem; }
            .hero-stats { gap: 1.5rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span style="color:#fff; font-size:1.4rem;">☰</span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#how">How it works</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#contact">Contact</a></li>
                @auth
                    <li class="nav-item"><a class="btn btn-quote btn-sm px-3" href="{{ route('dashboard') }}">Dashboard</a></li>
                @else
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="{{ route('login') }}">Log in</a></li>
                    <li class="nav-item"><a class="btn btn-quote btn-sm px-3" href="{{ route('register') }}">Get started</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge">● Trusted by 500+ businesses across West Africa</span>
                <h1>Moving freight,<br>building futures.</h1>
                <p class="lead mt-3">5050 Logistics Limited delivers dependable freight, warehousing and last-mile logistics — with real-time tracking and a fleet you can count on.</p>
                <div class="d-flex gap-3 flex-wrap mt-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-quote btn-lg px-4">Go to dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-quote btn-lg px-4">Get a quote</a>
                        <a href="#services" class="btn btn-outline-light-custom btn-lg px-4">Our services</a>
                    @endauth
                </div>
                <div class="hero-stats">
                    <div class="hero-stat"><div class="num">48K+</div><div class="lbl">Shipments / month</div></div>
                    <div class="hero-stat"><div class="num">96%</div><div class="lbl">On-time delivery</div></div>
                    <div class="hero-stat"><div class="num">120+</div><div class="lbl">Fleet vehicles</div></div>
                    <div class="hero-stat"><div class="num">15</div><div class="lbl">Years experience</div></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-img-wrap">
                    <img src="{{ asset('img/truck-highway.jpg') }}" alt="5050 Logistics truck on the highway" class="hero-img">
                    <div class="hero-img-badge">
                        <span class="pulse"></span>
                        <div>
                            <div class="fw-bold" style="font-size: 0.875rem; line-height: 1.2;">Live tracking</div>
                            <div style="font-size: 0.75rem; color: #6b7280;">Shipment #NG-2481 en route</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section" id="services">
    <div class="container">
        <div class="row align-items-end mb-5">
            <div class="col-lg-7">
                <h2 class="section-title">End-to-end logistics services</h2>
            </div>
            <div class="col-lg-5">
                <p class="section-sub mb-0">From first-mile pickup to final delivery, we handle every link in your supply chain.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card service-card p-4">
                    <div class="service-icon bg-primary-subtle text-primary">🚚</div>
                    <h5 class="fw-bold">Freight Transport</h5>
                    <p class="text-secondary small mb-0">FTL &amp; LTL road freight with GPS-tracked vehicles and guaranteed transit windows.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card service-card p-4">
                    <div class="service-icon bg-success-subtle text-success">🏭</div>
                    <h5 class="fw-bold">Warehousing</h5>
                    <p class="text-secondary small mb-0">Secure bonded storage, cross-docking and inventory management across 3 strategic hubs.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card service-card p-4">
                    <div class="service-icon bg-warning-subtle text-warning">📦</div>
                    <h5 class="fw-bold">Last-Mile Delivery</h5>
                    <p class="text-secondary small mb-0">Same-day and next-day delivery with proof-of-delivery and customer notifications.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card service-card p-4">
                    <div class="service-icon bg-info-subtle text-info">📊</div>
                    <h5 class="fw-bold">Fleet &amp; Tracking</h5>
                    <p class="text-secondary small mb-0">Real-time shipment visibility, route optimization and detailed performance analytics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-light" id="how">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">How it works</h2>
            <p class="section-sub mx-auto">Get your goods moving in three simple steps.</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <span class="step-num">1</span>
                <h5 class="fw-bold mt-2">Request a quote</h5>
                <p class="text-secondary small">Tell us your lane, volume and timeline — get a transparent price within hours.</p>
            </div>
            <div class="col-md-4">
                <span class="step-num">2</span>
                <h5 class="fw-bold mt-2">We pick up &amp; ship</h5>
                <p class="text-secondary small">Our fleet collects your cargo and moves it with live GPS tracking end to end.</p>
            </div>
            <div class="col-md-4">
                <span class="step-num">3</span>
                <h5 class="fw-bold mt-2">Track &amp; confirm</h5>
                <p class="text-secondary small">Monitor deliveries in real time and receive digital proof of delivery on completion.</p>
            </div>
        </div>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        <div class="row align-items-end mb-5">
            <div class="col-lg-7">
                <h2 class="section-title">Our fleet in action</h2>
            </div>
            <div class="col-lg-5">
                <p class="section-sub mb-0">From tipper trucks and haulage vehicles to bonded warehouses and delivery vans — every link covered.</p>
            </div>
        </div>
        <div class="fleet-grid">
            <div class="fleet-item">
                <img src="{{ asset('img/tipper-truck.jpg') }}" alt="Tipper truck" loading="lazy">
                <div class="fleet-label">Tipper &amp; dump trucks</div>
            </div>
            <div class="fleet-item">
                <img src="{{ asset('img/truck-highway.jpg') }}" alt="Haulage truck on highway" loading="lazy">
                <div class="fleet-label">Long-haul freight</div>
            </div>
            <div class="fleet-item">
                <img src="{{ asset('img/warehouse.jpg') }}" alt="Logistics warehouse" loading="lazy">
                <div class="fleet-label">Warehousing &amp; cross-dock</div>
            </div>
            <div class="fleet-item">
                <img src="{{ asset('img/delivery-van.jpg') }}" alt="Delivery van" loading="lazy">
                <div class="fleet-label">Last-mile delivery</div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-band">
            <h2 class="mb-3">Ready to ship smarter?</h2>
            <p class="mb-4 mx-auto" style="max-width: 32rem;">Join hundreds of businesses that trust 5050 Logistics Limited with their supply chain.</p>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-quote btn-lg px-4">Open dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-quote btn-lg px-4 me-2">Create account</a>
                <a href="{{ route('login') }}" class="btn btn-outline-light-custom btn-lg px-4">Log in</a>
            @endauth
        </div>
    </div>
</section>

<footer id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited" class="footer-logo">
                <p>Dependable freight, warehousing and last-mile logistics across Nigeria and West Africa.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Company</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#services">Services</a></li>
                    <li class="mb-2"><a href="#how">How it works</a></li>
                    <li class="mb-2"><a href="{{ route('login') }}">Log in</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Account</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('register') }}">Register</a></li>
                    <li class="mb-2"><a href="{{ route('password.request') }}">Forgot password</a></li>
                    @auth
                        <li class="mb-2"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-lg-4">
                <h6>Contact</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">📍 12 Marina Road, Lagos, Nigeria</li>
                    <li class="mb-2">📞 Telephone: +234 706 649 6268</li>
                    <li class="mb-2">✉️ <a href="mailto:info@fiftyfiftylogistics.com">info@fiftyfiftylogistics.com</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <span>© {{ date('Y') }} 5050 Logistics Limited. All rights reserved.</span>
            <span>Moving freight, building futures.</span>
        </div>
    </div>
</footer>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>
