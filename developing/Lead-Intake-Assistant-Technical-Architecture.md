# Lead Intake Assistant - Technical Architecture Specification (AI-First)

## Overview

Lead Intake Assistant is a multi-tenant SaaS platform that qualifies leads before they enter the customer's existing workflow.

The platform uses AI-driven conversations to collect structured information through natural dialogue.

The objective is not to replace CRMs or job management software.

The objective is to:

- Capture leads
- Qualify leads
- Collect media and supporting information
- Generate structured summaries
- Deliver qualified opportunities to businesses

The platform stops at lead qualification.

---

# Core Product Philosophy

Traditional forms create friction.

The platform should feel like a conversation, not a form.

Customers should interact with an AI assistant that:

- Understands intent
- Adapts questions dynamically
- Handles follow-up questions
- Collects required information naturally

The system must still guarantee structured output.

AI controls the conversation.

The platform controls business rules and completion requirements.

---

# Architecture Principles

## Principle 1: AI-Driven Conversations

AI is a first-class component.

The AI should:

- Generate questions
- Adapt wording
- Interpret responses
- Handle conditional flows
- Extract structured information
- Generate summaries

The user should never feel like they are filling out a traditional form.

---

## Principle 2: Structured Data Always Wins

AI conversations must produce structured output.

Every conversation should map into normalized fields.

Example:

Customer:

"I need a new tiled roof because mine is leaking."

Extracted fields:

- problem_type
- roof_type
- urgency

Structured information remains the source of truth.

---

## Principle 3: System Controls Completion

AI should not decide when a lead is complete.

The platform should determine:

- Required fields
- Missing information
- Qualification status
- Completion criteria

This prevents incomplete leads.

---

## Principle 4: Industry Intelligence

Each industry has its own requirements.

Examples:

Roofing:
- Roof type
- Leak information
- Roof age

HVAC:
- System type
- Heating/cooling issue

Plumbing:
- Pipe issue
- Emergency status

Industry behavior should be configurable.

---

# Technology Stack

## Backend

- Laravel 13
- PHP 8.4

Requirements:

- Queues
- Events
- Notifications
- API Resources
- Policies
- Service Classes

---

## Admin Panel

- Filament 5
- Livewire 4

Responsibilities:

- Tenant management
- Lead review
- Templates
- Billing
- Settings
- Analytics

Filament is for administrators only.

Never use Filament for public lead qualification experiences.

---

## Customer-Facing Widget

Separate frontend application.

Embedded using:

<script src="https://widget.domain.com/widget.js"></script>

Requirements:

- Mobile-first
- Lightweight
- Framework agnostic
- Fast loading

Must work on:

- WordPress
- Shopify
- Static websites
- Custom websites

---

# High-Level Services

## Tenant Service

Responsibilities:

- Multi-tenancy
- Branding
- Subscription ownership
- Qualification configuration

---

## Lead Service

Responsibilities:

- Lead creation
- Lead lifecycle management
- Source tracking
- Qualification status

Lead states:

- New
- In Progress
- Qualified
- Delivered

No CRM stages.

---

## Conversation Orchestrator

Central component of the system.

Responsibilities:

1. Receive customer message
2. Load conversation context
3. Load qualification requirements
4. Send context to LLM
5. Extract structured information
6. Validate extracted fields
7. Identify missing fields
8. Generate next response
9. Persist conversation state

This service is the heart of the platform.

---

## Qualification Intelligence Engine

Purpose:

Ensure all required information is collected.

Responsibilities:

- Track required fields
- Track optional fields
- Validate collected information
- Detect missing information
- Determine completion

The engine owns qualification logic.

Not the LLM.

---

## Industry Configuration Engine

Purpose:

Allow industries to be configured without code changes.

Example:

Roofing configuration:

- Required fields
- Optional fields
- Industry rules
- AI instructions

Industry behavior should be configurable.

---

## Media Service

Responsibilities:

- Upload handling
- Storage
- Image optimization
- Virus scanning
- Secure access

Supported:

- Images
- PDFs

Future:

- Videos

---

## Summary Service

Purpose:

Generate contractor-friendly summaries.

Input:

- Structured fields
- Uploaded media
- Conversation history

Output:

- Executive summary
- Lead details
- Recommended actions

AI-powered.

---

## Lead Scoring Service

Purpose:

Estimate lead quality.

Signals:

- Project type
- Urgency
- Media provided
- Information completeness

Can be rule-based initially.

AI may assist later.

---

## Notification Service

Responsibilities:

- Email delivery
- SMS delivery

Future:

- WhatsApp
- CRM integrations

Provider-agnostic design.

---

# AI Architecture

## Design Goal

The AI should conduct natural conversations while producing structured output.

---

## AI Responsibilities

Allowed:

- Ask questions
- Interpret responses
- Generate follow-ups
- Handle branching conversations
- Generate summaries
- Extract structured fields

---

## AI Restrictions

The AI cannot:

- Mark leads complete
- Define business rules
- Decide required fields
- Control qualification requirements

Those belong to the platform.

---

# Structured Extraction Model

Example:

Customer:

"I need to replace my asbestos roof."

Extract:

{
  "problem_type": "replacement",
  "roof_type": "asbestos"
}

The platform updates qualification state.

The AI then determines the best next question.

---

# Industry Configuration Model

Industries should be configurable using structured definitions.

Example:

industry: roofing

required_fields:
- contact_name
- phone
- address
- problem_type
- roof_type

optional_fields:
- roof_age
- urgency
- insurance_claim

rules:
- if roof_type=asbestos collect asbestos-specific information
- if problem_type=replacement collect roof_size
- if problem_type=repair collect leak_location

---

# Widget Architecture

## Installation

Single script tag.

Goal:

Installation under 5 minutes.

---

## Widget Flow

1. Widget loads
2. Tenant configuration loaded
3. AI conversation starts
4. Qualification progresses
5. Lead completes
6. Notification sent

---

## Widget Requirements

Must support:

- Mobile devices
- Desktop devices
- Camera uploads
- Multiple file uploads
- Session recovery

Users must be able to resume conversations.

---

# Missed Call Recovery

## Goal

Convert missed calls into qualified leads.

---

## Flow

Customer calls.

Call is unanswered.

Call is forwarded to tracking number.

Platform detects missed call.

SMS sent automatically.

Customer opens qualification link.

AI intake conversation begins.

Lead is qualified.

Lead is delivered.

---

## SMS Example

Thanks for contacting ABC Roofing.

Please answer a few quick questions so we can help faster:

[Start Qualification]

---

# Multi-Tenancy

Every record belongs to a tenant.

All queries must be tenant-scoped.

Tenant configuration includes:

- Branding
- AI instructions
- Industry templates
- Notification settings

---

# Authentication

## Admin Users

Support:

- Email/password
- Password reset

Future:

- Google OAuth

---

## Customers

No authentication required.

Use:

- Signed URLs
- Session tokens

---

# Queue Architecture

All expensive operations must be queued.

Examples:

- AI calls
- Image processing
- Summary generation
- Notifications
- Lead scoring

Never block customer interactions.

---

# Storage

## Database

Preferred:

- PostgreSQL

---

## Files

Preferred:

- Cloudflare R2

Alternative:

- S3

Avoid local storage.

---

# AI Provider Layer

Create provider abstraction.

Supported providers:

- OpenAI
- Anthropic

Future:

- Self-hosted models

Vendor lock-in should be avoided.

---

# Billing

Recommended:

Stripe

Plans:

Starter
- Widget
- AI Qualification

Professional
- Widget
- AI Qualification
- Missed Call Recovery

---

# Monitoring

Track:

- Widget loads
- Conversation starts
- Qualification completion rate
- AI failures
- Notification failures
- Upload failures

---

# Logging

Log:

- Lead lifecycle events
- Qualification events
- AI interactions metadata
- Notification events

Do not log sensitive media contents.

---

# Success Metrics

Business:

- Leads captured
- Qualification completion rate
- Time to qualification
- Qualified leads delivered

Product:

- Widget performance
- AI response times
- Upload success rate
- Notification success rate

---

# Explicit Non-Goals

Do not build:

- CRM
- Kanban boards
- Pipelines
- Scheduling
- Calendars
- Invoicing
- Estimates
- Payments
- Job management
- Customer portals

The platform exists solely to qualify leads and deliver structured opportunities to businesses.
