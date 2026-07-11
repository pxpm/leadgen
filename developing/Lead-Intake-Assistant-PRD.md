# Lead Intake Assistant – Product Requirements Document (PRD)

## Vision

A lead qualification platform for trade businesses that sits before existing CRM/job management software.

The goal is not to replace CRMs, schedulers, invoicing systems, or job management platforms.

The goal is to:

- Respond instantly to inbound leads
- Collect structured information
- Collect photos and supporting media
- Qualify the lead automatically
- Deliver a complete lead package to the business

Core value proposition:

> Every lead gets an immediate response and arrives pre-qualified.

---

# Target Customer

Initial niche:

- Roofing contractors

Future niches:

- Solar installers
- HVAC companies
- Plumbers
- Landscapers
- Renovation companies

Characteristics:

- High-ticket projects
- Slow lead response times
- Limited office staff
- Owners frequently on-site
- Revenue impact from missed or poorly qualified leads

---

# Problems Being Solved

## Problem 1

Leads arrive while the owner is busy working on-site.

Result:

- Delayed responses
- Lost opportunities
- Lower conversion rates

---

## Problem 2

Businesses spend time gathering the same information repeatedly.

Examples:

- Address
- Issue description
- Photos
- Urgency
- Insurance information

Result:

- Administrative burden
- Slow quoting process

---

## Problem 3

Website forms provide poor-quality information.

Example:

Customer submits:

"I need help with my roof."

The business must then spend time collecting the missing details.

---

# Product Scope

## Included

### Website Chat Widget

Embedded on customer websites.

Functions:

- Start qualification instantly
- Collect lead information
- Collect images
- Collect contact information
- Guide the user through a structured intake flow

---

### Lead Qualification Flow

Industry-specific questioning.

Examples for roofing:

- Address
- Type of issue
- Roof age
- Leak or damage
- Insurance claim
- Urgency
- Photo uploads

---

### Lead Summary Generation

Generate a structured summary from collected information.

Output:

- Customer details
- Property details
- Issue summary
- Uploaded media
- Urgency level
- Qualification score

---

### Notifications

Notify the contractor when a lead is completed.

Channels:

- Email
- SMS

---

### Missed Call Recovery

If a call is not answered:

- Trigger SMS automatically
- Send intake link
- Start qualification flow

Goal:

Convert missed calls into qualified leads.

---

## Excluded

Do not build:

- CRM
- Sales pipeline
- Scheduling
- Calendar management
- Invoicing
- Payments
- Job management
- Team management
- Customer portal

The product ends when the lead is qualified and delivered.

---

# User Personas

## Homeowner

Goals:

- Get help quickly
- Avoid waiting for callbacks
- Submit information once

Success Criteria:

- Qualification completed in under 3 minutes
- Easy photo uploads
- Mobile-friendly experience

---

## Roofing Business Owner

Goals:

- Respond instantly
- Reduce admin work
- Receive complete information

Success Criteria:

- No manual qualification needed
- Better lead quality
- Faster quote preparation

---

## Office Manager

Goals:

- Avoid repetitive questioning
- Organize incoming leads

Success Criteria:

- Structured lead summaries
- Consistent information collection

---

# User Stories

## Website Visitor Journey

As a homeowner,

I want to request a quote online,

So that I can quickly get help.

### Flow

1. Visit website
2. Open widget
3. Answer qualification questions
4. Upload photos
5. Submit contact information
6. Receive confirmation

Outcome:

Lead is delivered to the contractor.

---

## Contractor Journey

As a contractor,

I want leads to arrive already qualified,

So that I can focus on quoting and selling.

### Flow

1. Lead arrives
2. Summary is generated
3. Photos attached
4. Notification sent
5. Contractor reviews lead
6. Contractor contacts customer

Outcome:

No qualification calls required.

---

## Missed Call Journey

As a contractor,

I want missed callers to be automatically engaged,

So that I don't lose potential jobs.

### Flow

1. Customer calls
2. Call not answered
3. System detects missed call
4. SMS sent automatically
5. Customer opens intake flow
6. Qualification completed
7. Lead delivered

Outcome:

Missed calls become qualified opportunities.

---

# Qualification Engine

## Design Principle

Do not start with AI-driven conversations.

Start with structured workflows.

Benefits:

- Predictable
- Easier to test
- Easier to customize
- Lower cost

---

## Industry Templates

### Roofing Template

Example questions:

1. What is your address?
2. What issue are you experiencing?
3. Is the roof currently leaking?
4. Can you upload photos?
5. Is this an insurance claim?
6. How urgent is the issue?
7. What is the best phone number?

---

# AI Usage

AI should be used only where it creates clear value.

## Lead Summary Generation

Convert collected answers into:

- Human-readable summary
- Key facts
- Recommended next actions

---

## Lead Scoring

Assign a score based on:

- Completeness
- Urgency
- Media provided
- Estimated project value

---

## Missing Information Detection

Identify important information that was not collected.

Example:

"Roof age not provided."

---

# Website Widget Requirements

## Installation

Single script embed.

Goal:

Less than 5 minutes to install.

---

## Mobile First

Most homeowners will use mobile devices.

Requirements:

- Responsive
- Fast loading
- Easy image uploads
- Large touch targets

---

## Branding

Per-company customization:

- Logo
- Business name
- Colors
- Intro message

---

# Missed Call Recovery Requirements

## Objective

Recover leads from unanswered calls.

---

## Workflow

1. Missed call occurs
2. SMS sent automatically
3. Customer receives intake link
4. Qualification starts
5. Lead submitted
6. Summary delivered

---

## Messaging Principles

Keep messages short.

Example:

"Thanks for calling. We're currently helping another customer. Please answer a few quick questions and upload photos so we can assist you faster."

---

# Notifications

## Email Notification

Must include:

- Customer information
- Lead summary
- Uploaded media links
- Qualification score

---

## SMS Notification

Short version:

- Customer name
- Urgency
- Link to lead details

---

# SaaS Requirements

## Multi-Tenant

Each company must have:

- Separate leads
- Separate branding
- Separate qualification templates
- Separate notifications

---

## Subscription Model

Possible pricing:

Starter:
- Website widget
- Lead qualification

Professional:
- Website widget
- Lead qualification
- Missed call recovery

---

# Analytics

Keep minimal initially.

Metrics:

- Leads received
- Leads completed
- Completion rate
- Average qualification time
- Source breakdown

---

# MVP Success Criteria

A customer should be able to:

1. Sign up
2. Install widget
3. Receive qualified leads

Within the same day.

If onboarding requires calls, technical support, or complex integrations, simplify further.

---

# Product Principles

1. Do not build a CRM.
2. Do not build scheduling.
3. Do not build invoicing.
4. Do not build job management.
5. Solve qualification only.
6. Optimize for fast onboarding.
7. Optimize for the first paying customer.
8. Reduce friction wherever possible.
9. Deliver structured information.
10. Make lead response immediate.
