# Lead Intake Assistant - Customer Configuration Specification

## Purpose

This document defines how industries, customers, qualification requirements, AI behavior, lead scoring, branding, and notifications are configured.

The goal is to make new verticals configurable without requiring application code changes.

A new industry should ideally be onboarded by configuration only.

---

# Configuration Hierarchy

Configuration should be resolved in the following order:

Global Defaults
↓
Industry Configuration
↓
Customer Configuration
↓
Lead-Specific Runtime Context

Customer-specific configuration always overrides industry defaults.

---

# Industry Configuration

Each industry defines:

- Qualification requirements
- AI instructions
- Lead scoring rules
- Summary behavior
- Media requirements

Examples:

- Roofing
- HVAC
- Plumbing
- Solar
- Landscaping

---

# Industry Definition Structure

Example:

industry: roofing

name: Roofing Contractors

description: Roof repairs, replacements and inspections

---

# Qualification Configuration

Defines what information is required before a lead is considered qualified.

## Required Fields

Required fields must be collected before qualification can complete.

Example:

required_fields:
  - contact_name
  - phone
  - property_address
  - problem_type
  - roof_type

---

## Optional Fields

Optional fields improve lead quality but do not block completion.

Example:

optional_fields:
  - roof_age
  - urgency
  - insurance_claim
  - roof_size

---

# Conditional Requirements

Certain fields become required depending on context.

Example:

If:

problem_type = replacement

Require:

roof_size

---

Example:

If:

roof_type = asbestos

Require:

asbestos_removal_required

---

# AI Configuration

## Purpose

Controls AI behavior for a specific industry.

---

## Industry Context

Example:

You are assisting homeowners requesting roofing services.

Your objective is to gather enough information for a contractor to prepare an estimate or site visit.

Keep responses concise and conversational.

---

## AI Goals

Examples:

- Understand the issue
- Gather project details
- Collect supporting media
- Determine urgency
- Capture contact information

---

## AI Constraints

Examples:

- Never provide legal advice
- Never provide engineering advice
- Never estimate project costs
- Never invent information

---

# Industry Knowledge Rules

Industry-specific rules should be configurable.

Example:

Roofing

Rules:

- Leaks require leak location
- Replacements require roof size
- Asbestos roofs require asbestos-specific questions

---

Example:

HVAC

Rules:

- Collect system type
- Collect heating or cooling issue
- Determine emergency status

---

# Conversation Configuration

## Opening Message

Example:

Hi, I'd be happy to help with your roofing project. Can you tell me a little about what you need?

---

## Tone

Options:

- Professional
- Friendly
- Formal
- Casual

Default:

Professional and friendly

---

## Response Length

Options:

- Short
- Medium
- Detailed

Default:

Short

---

# Media Requirements

Defines when media should be requested.

---

## Recommended Uploads

Roofing:

- Roof photos
- Damage photos
- Leak area photos

---

HVAC:

- Equipment photos
- Error displays

---

Plumbing:

- Leak photos
- Pipe photos

---

# Lead Scoring Configuration

## Purpose

Determine lead quality.

---

## Scoring Factors

Example:

Photos Uploaded:
+2

Urgency Provided:
+1

Address Provided:
+1

Project Type Known:
+1

Insurance Claim:
+1

Replacement Project:
+2

---

## Scoring Ranges

1-3
Low Quality

4-7
Medium Quality

8-10
High Quality

---

# Summary Configuration

Defines how lead summaries are generated.

---

## Executive Summary

Short overview for business owner.

---

## Structured Summary

All extracted fields.

---

## Missing Information Section

Any optional fields not collected.

---

## Recommended Actions

Suggested next steps.

Example:

Customer reports active leak.

Recommend contacting within 30 minutes.

---

# Notification Configuration

Each customer controls notification behavior.

---

## Email Notifications

Configuration:

enabled: true

recipient_addresses:
  - owner@example.com

---

## SMS Notifications

Configuration:

enabled: true

recipient_numbers:
  - +351xxxxxxxxx

---

# Branding Configuration

Each customer may customize:

- Logo
- Brand colors
- Company name
- Welcome message

---

## Widget Appearance

Configuration:

Primary color

Secondary color

Logo URL

Business name

---

# Customer-Specific Overrides

Customers may override industry defaults.

Example:

Industry requires:

roof_type

Customer may additionally require:

insurance_claim

---

Example:

Industry opening message:

Hi, how can we help?

Customer override:

Welcome to ABC Roofing. Tell us about your project.

---

# Lead Sources

Track source of lead.

Supported:

- Website widget
- Missed call recovery
- Direct intake link

Future:

- WhatsApp
- Instagram
- Facebook Messenger

---

# Localization

Support multiple languages.

Configuration:

Default language

Supported languages

Fallback language

---

# Qualification Completion Rules

Lead is qualified when:

1. All required fields exist
2. Validation succeeds
3. Contact information exists

Optional fields never block qualification.

---

# Validation Rules

Examples:

Phone:
Valid format required

Email:
Valid format required

Address:
Minimum confidence threshold

---

# Confidence Thresholds

AI extraction confidence should be stored.

Example:

contact_name:
0.98

roof_type:
0.92

If confidence falls below threshold:

Request clarification.

---

# Customer Onboarding Defaults

When a new customer signs up:

1. Select industry
2. Apply industry defaults
3. Configure branding
4. Configure notifications
5. Install widget

Customer should be operational within minutes.

---

# Future Vertical Expansion

The platform should support adding industries through configuration only.

Adding:

- Roofing
- HVAC
- Plumbing
- Landscaping
- Solar

should not require application code changes.

---

# Success Criteria

A new industry can be launched by:

1. Creating industry configuration
2. Defining required fields
3. Defining AI instructions
4. Defining scoring rules
5. Defining summary rules

No engineering work should be required.

The configuration system should become the foundation for rapid vertical expansion.
