# TrainerWorkspace

TrainerWorkspace is a web application for personal trainers and group coaches.

It helps trainers manage clients and groups, organize their schedule, create structured training plans, track completed sessions, and keep notes about individual progress, restrictions, and training history.

The application also includes AI-assisted training plan generation. AI drafts are created using real context from the workspace, including trainee or group goals, restrictions, previous sessions, trainer notes, long-term memory, and a controlled exercise library.

## Key Features

- Trainee and training group management
- Daily schedule and calendar
- Custom trainer working hours
- Exercise library with system and personal exercises
- Manual training plan builder
- AI-generated training plan drafts
- Structured training blocks and exercises
- Training history
- Post-training notes
- Long-term memory for trainees and groups
- AI request logging and validation
- Support for OpenRouter and LM Studio

## Tech Stack

- Laravel 13
- PHP
- React with TypeScript
- Inertia.js
- PostgreSQL
- Redis (not now)
- Docker and Laravel Sail
- Tailwind CSS
- OpenRouter and LM Studio compatible AI API

## Roadmap

- Add RAG for more context-aware plan generation
- Move AI generation to Redis-backed background jobs
- Improve the overall UI/UX
- Reduce AI response time and generation costs

## AI Workflow

TrainerWorkspace does not save raw AI responses directly as training plans.

The generation flow is:

```text
Scheduled Training
→ Context Builder
→ Prompt Builder
→ AI Provider
→ Response Validator
→ Draft Training Plan


