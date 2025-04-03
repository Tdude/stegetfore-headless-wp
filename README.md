# Headless Wordpress theme

This is a WP theme with a decoupled Next/React companion. First time I try it so there definitively is tweaking to do. So far so good.

# Docs docs docs

## Overview of the Module System and some guiding

This theme provides a flexible way to create, manage, and reuse content blocks across pages. Each module has a specific template type (hero, testimonials, etc.) and can be assigned to pages through a custom meta box. I have tried to keep it generic. Most of the time you can add one or many.

### First a quick note

If you don't like the "stegetfore" namespace, use your favourite editor to search and replace. For your terminal you can use the "sed" command but you're on your own.
`find . -type f -exec sed -i 's/old_word/new_word/g' {} \;`

## Module Architecture

### Core Components

1. **Module Custom Post Type**: Foundation for creating reusable content blocks
2. **Module Templates**: Different content types like hero, selling points, testimonials, etc.
3. **Module Taxonomies**: Categories and placements for organizing modules
4. **Module Meta Fields**: Custom fields for each module type
5. **Module Admin UI**: Custom admin interface for managing modules
6. **Module REST API**: Endpoints to access module data from the frontend

### Module Templates

The following module templates are available:

| Template         | Description                               | Specific Fields                              |
| ---------------- | ----------------------------------------- | -------------------------------------------- |
| `hero`           | Hero banner with image, text, and buttons | Overlay opacity, text color, height          |
| `selling_points` | Feature highlights with icons             | List of points with title, description, icon |
| `stats`          | Statistical information display           | List of stats with value, label, icon        |
| `testimonials`   | Customer/client testimonials              | Testimonial IDs to display                   |
| `gallery`        | Image gallery                             | Gallery image IDs                            |
| `faq`            | Frequently asked questions with accordion | List of questions and answers                |
| `tabbed_content` | Content organized in tabs                 | List of tabs with title and content          |
| `charts`         | Data visualizations                       | Chart type, labels, datasets                 |
| `sharing`        | Social sharing buttons                    | Networks to display                          |
| `login`          | User login form                           | Redirect URL, show register/lost password    |
| `payment`        | Payment form integration                  | Payment gateway, amount, currency            |
| `calendar`       | Calendar or date picker                   | Calendar type, min/max dates                 |
| `cta`            | Call to action with buttons               | Uses content editor and buttons              |
| `text_media`     | Text with image/media                     | Uses content editor and featured image       |
| `video`          | Video embed                               | Video URL                                    |
| `form`           | Contact or custom form                    | Form ID (Contact Form 7)                     |

## Working with Modules in the Frontend

### Fetching All Modules for a Page

To get all modules associated with a page:

```javascript
async function getPageWithModules(pageId) {
  const response = await fetch(`/wp-json/wp/v2/pages/${pageId}?_embed`);
  const pageData = await response.json();

  // Modules are available in the 'modules' field
  const modules = pageData.modules || [];

  return {
    page: pageData,
    modules: modules,
  };
}
```

### Fetching Specific Module Types

To get modules of a specific type:

```javascript
async function getModulesByTemplate(template) {
  const response = await fetch(
    `/wp-json/steget/v1/modules?template=${template}`
  );
  const data = await response.json();
  return data.modules || [];
}

// Example: Get all hero modules
const heroModules = await getModulesByTemplate("hero");
```

### Rendering Modules in React

Here's an example of a React component that renders different module types:

```jsx
import React from "react";
import HeroModule from "./modules/HeroModule";
import SellingPointsModule from "./modules/SellingPointsModule";
import TestimonialsModule from "./modules/TestimonialsModule";
import GalleryModule from "./modules/GalleryModule";
// Import other module components...

const ModuleRenderer = ({ module }) => {
  // Choose the appropriate component based on module template
  switch (module.template) {
    case "hero":
      return <HeroModule data={module} />;
    case "selling_points":
      return <SellingPointsModule data={module} />;
    case "testimonials":
      return <TestimonialsModule data={module} />;
    case "gallery":
      return <GalleryModule data={module} />;
    case "cta":
      return <CTAModule data={module} />;
    // Add cases for other module types...
    default:
      return <div>Unknown module type: {module.template}</div>;
  }
};

const PageModules = ({ modules }) => {
  return (
    <div className="page-modules">
      {modules.map((module, index) => (
        <div
          key={`module-${module.id}-${index}`}
          className={`module-wrapper module-${module.template} ${
            module.full_width ? "full-width" : ""
          }`}
          style={
            module.background_color
              ? { backgroundColor: module.background_color }
              : {}
          }
        >
          <div
            className={`module-container layout-${module.layout || "center"}`}
          >
            <ModuleRenderer module={module} />
          </div>
        </div>
      ))}
    </div>
  );
};

export default PageModules;
```

## Implementation Examples for Different Module Types

### Hero Module

```jsx
const HeroModule = ({ data }) => {
  const {
    title,
    content,
    featured_image,
    hero_settings = {},
    buttons = [],
  } = data;

  const {
    height = "medium",
    overlay_opacity = 0.3,
    text_color = "#ffffff",
  } = hero_settings;

  return (
    <div className={`hero-module hero-height-${height}`}>
      {featured_image && (
        <div
          className="hero-background"
          style={{ backgroundImage: `url(${featured_image})` }}
        >
          <div
            className="hero-overlay"
            style={{ opacity: overlay_opacity }}
          ></div>
        </div>
      )}
      <div className="hero-content" style={{ color: text_color }}>
        <h1>{title}</h1>
        <div dangerouslySetInnerHTML={{ __html: content }} />

        {buttons.length > 0 && (
          <div className="hero-buttons">
            {buttons.map((button, i) => (
              <a
                key={i}
                href={button.url}
                className={`button button-${button.style || "primary"}`}
                target={button.new_tab ? "_blank" : "_self"}
              >
                {button.text}
              </a>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};
```

### Selling Points Module

```jsx
const SellingPointsModule = ({ data }) => {
  const { title, content, selling_points = [] } = data;

  return (
    <div className="selling-points-module">
      <div className="module-header">
        <h2>{title}</h2>
        <div dangerouslySetInnerHTML={{ __html: content }} />
      </div>

      <div className="selling-points-grid">
        {selling_points.map((point, i) => (
          <div key={i} className="selling-point">
            {point.icon && (
              <div className="selling-point-icon">
                {/* Render icon based on point.icon */}
                <i className={point.icon}></i>
              </div>
            )}
            <h3>{point.title}</h3>
            <p>{point.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};
```

### Testimonials Module

```jsx
const TestimonialsModule = ({ data }) => {
  const { title, content, testimonials = [] } = data;

  return (
    <div className="testimonials-module">
      <div className="module-header">
        <h2>{title}</h2>
        <div dangerouslySetInnerHTML={{ __html: content }} />
      </div>

      <div className="testimonials-slider">
        {testimonials.map((testimonial, i) => (
          <div key={i} className="testimonial">
            <div className="testimonial-content">
              <div dangerouslySetInnerHTML={{ __html: testimonial.content }} />
            </div>
            <div className="testimonial-author">
              {testimonial.author_image && (
                <div className="author-image">
                  <img
                    src={testimonial.author_image}
                    alt={testimonial.author_name}
                  />
                </div>
              )}
              <div className="author-info">
                <div className="author-name">{testimonial.author_name}</div>
                {testimonial.author_position && (
                  <div className="author-position">
                    {testimonial.author_position}
                  </div>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
```

### CTA Module

```jsx
const CTAModule = ({ data }) => {
  const { title, content, buttons = [] } = data;

  return (
    <div className="cta-module">
      <h2>{title}</h2>
      <div dangerouslySetInnerHTML={{ __html: content }} />

      {buttons.length > 0 && (
        <div className="cta-buttons">
          {buttons.map((button, i) => (
            <a
              key={i}
              href={button.url}
              className={`button button-${button.style || "primary"}`}
              target={button.new_tab ? "_blank" : "_self"}
            >
              {button.text}
            </a>
          ))}
        </div>
      )}
    </div>
  );
};
```

### Form Module

```jsx
const FormModule = ({ data }) => {
  const { title, content, form_id } = data;

  // Import your contact form component
  // This assumes you have a ContactForm component like the one provided earlier
  const ContactForm = dynamic(() => import("../components/ContactForm"), {
    ssr: false,
  });

  return (
    <div className="form-module">
      <h2>{title}</h2>
      <div dangerouslySetInnerHTML={{ __html: content }} />

      {form_id && (
        <div className="form-container">
          <ContactForm formId={form_id} />
        </div>
      )}
    </div>
  );
};
```

## Styling Considerations

Each module should have its own styling based on the layout and full-width settings. Here's a simplified example of CSS to handle different layouts:

```css
/* Base module styling */
.module-wrapper {
  padding: 4rem 2rem;
}

/* Layout variations */
.module-container.layout-center {
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
}

.module-container.layout-left {
  max-width: 1200px;
  margin: 0 auto;
  text-align: left;
}

.module-container.layout-right {
  max-width: 1200px;
  margin: 0 auto;
  text-align: right;
}

/* Full-width handling */
.module-wrapper.full-width {
  padding-left: 0;
  padding-right: 0;
}

.module-wrapper.full-width .module-container {
  max-width: 100%;
  width: 100%;
}
```

## Advanced Module Implementation

### Dynamic Loading of Modules

For better performance, consider dynamically loading modules only when needed:

```jsx
import dynamic from "next/dynamic";

// Dynamically import module components
const moduleComponents = {
  hero: dynamic(() => import("./modules/HeroModule")),
  selling_points: dynamic(() => import("./modules/SellingPointsModule")),
  testimonials: dynamic(() => import("./modules/TestimonialsModule")),
  // Add other module types...
};

const DynamicModuleRenderer = ({ module }) => {
  const ModuleComponent = moduleComponents[module.template];

  if (!ModuleComponent) {
    return <div>Unknown module type: {module.template}</div>;
  }

  return <ModuleComponent data={module} />;
};
```

### Module Page Builder

Here's a complete example of a page builder component that fetches and renders all modules for a page:

```jsx
import { useEffect, useState } from "react";
import dynamic from "next/dynamic";

// Import a loading component
import LoadingSpinner from "../components/LoadingSpinner";

const moduleComponents = {
  hero: dynamic(() => import("../components/modules/HeroModule")),
  selling_points: dynamic(() =>
    import("../components/modules/SellingPointsModule")
  ),
  testimonials: dynamic(() =>
    import("../components/modules/TestimonialsModule")
  ),
  gallery: dynamic(() => import("../components/modules/GalleryModule")),
  cta: dynamic(() => import("../components/modules/CTAModule")),
  form: dynamic(() => import("../components/modules/FormModule")),
  // Add other module types...
};

const PageBuilder = ({ pageId }) => {
  const [page, setPage] = useState(null);
  const [modules, setModules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchPageData = async () => {
      try {
        setLoading(true);
        const response = await fetch(`/wp-json/wp/v2/pages/${pageId}?_embed`);

        if (!response.ok) {
          throw new Error(`Failed to fetch page data: ${response.status}`);
        }

        const pageData = await response.json();
        setPage(pageData);

        // Page modules are included in the page data response
        // thanks to our custom REST field
        if (pageData.modules && Array.isArray(pageData.modules)) {
          setModules(pageData.modules);
        }

        setLoading(false);
      } catch (err) {
        console.error("Error fetching page data:", err);
        setError(err.message);
        setLoading(false);
      }
    };

    if (pageId) {
      fetchPageData();
    }
  }, [pageId]);

  if (loading) {
    return <LoadingSpinner />;
  }

  if (error) {
    return <div className="error-message">Error loading page: {error}</div>;
  }

  if (!page) {
    return <div className="not-found">Page not found</div>;
  }

  return (
    <div className="page-content">
      {/* Page title could be displayed separately or handled by a specific module */}
      {/* <h1>{page.title.rendered}</h1> */}

      {/* Render modules */}
      <div className="page-modules">
        {modules.map((module, index) => {
          const ModuleComponent = moduleComponents[module.template];

          if (!ModuleComponent) {
            return (
              <div key={index} className="unknown-module">
                Unknown module type: {module.template}
              </div>
            );
          }

          return (
            <div
              key={`module-${module.id}-${index}`}
              className={`module-wrapper module-${module.template} ${
                module.full_width ? "full-width" : ""
              }`}
              style={
                module.background_color
                  ? { backgroundColor: module.background_color }
                  : {}
              }
            >
              <div
                className={`module-container layout-${
                  module.layout || "center"
                }`}
              >
                <ModuleComponent data={module} />
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default PageBuilder;
```

## Evaluation System API

The evaluation system provides endpoints for managing student evaluations and retrieving evaluation questions.

### Authentication Requirements

The evaluation system uses the Headless Access Manager (HAM) plugin for JWT authentication. Most endpoints require authentication, but the evaluation questions structure is available through a public endpoint.

### Evaluation Endpoints

| Endpoint | Method | Auth Required | Description |
|----------|--------|---------------|-------------|
| `/wp-json/ham/v1/evaluation/save` | POST | Yes | Save or update an evaluation |
| `/wp-json/ham/v1/evaluation/get/{id}` | GET | Yes | Get a specific evaluation by ID |
| `/wp-json/ham/v1/evaluation/questions` | GET | Yes | Get evaluation questions structure (requires JWT auth) |
| `/wp-json/public/v1/evaluation/questions` | GET | No | Public endpoint for evaluation questions structure |

### Using the Public Evaluation Questions Endpoint

The public endpoint for evaluation questions was created to allow access without JWT authentication. This is particularly useful for initial form loading before a user is authenticated.

Example of fetching evaluation questions using the public endpoint:

```javascript
// Using fetch directly
async function getEvaluationQuestions() {
  const response = await fetch('/wp-json/public/v1/evaluation/questions');
  const data = await response.json();
  return data;
}

// Using the API client from formTryggveApi.ts
import { evaluationApi } from '@/lib/api/formTryggveApi';

async function loadEvaluationQuestions() {
  try {
    const questionsData = await evaluationApi.getQuestionsStructure();
    // Process the questions data
    return questionsData;
  } catch (error) {
    console.error('Error loading evaluation questions:', error);
  }
}
```

### Evaluation Questions Structure

The evaluation questions endpoint returns a structured object with sections and questions:

```json
{
  "anknytning": {
    "title": "Anknytning",
    "questions": {
      "a1": {
        "text": "Eleven söker kontakt med läraren vid behov",
        "options": [
          {"value": "1", "label": "1", "stage": "ej"},
          {"value": "2", "label": "2", "stage": "ej"},
          {"value": "3", "label": "3", "stage": "trans"},
          {"value": "4", "label": "4", "stage": "trans"},
          {"value": "5", "label": "5", "stage": "full"}
        ]
      },
      "a2": {
        "text": "Eleven tar emot tröst från läraren",
        "options": [
          {"value": "1", "label": "1", "stage": "ej"},
          {"value": "2", "label": "2", "stage": "ej"},
          {"value": "3", "label": "3", "stage": "trans"},
          {"value": "4", "label": "4", "stage": "trans"},
          {"value": "5", "label": "5", "stage": "full"}
        ]
      }
    }
  },
  "ansvar": {
    "title": "Ansvar",
    "questions": {
      // More questions here...
    }
  }
}
```

## API Endpoints Reference

All the API endpoints available in the headless WordPress theme, organized by category for easy reference.

## Core Endpoints

| Endpoint                             | Method | Description                                                        |
| ------------------------------------ | ------ | ------------------------------------------------------------------ |
| `/wp-json/steget/v1/test`            | GET    | Test endpoint to check if the API is working                       |
| `/wp-json/steget/v1/site-info`       | GET    | Get basic site information (name, description, URL, etc.)          |
| `/wp-json/steget/v1/posts-extended`  | GET    | Get extended posts data with custom fields                         |
| `/wp-json/steget/v1/menu/{location}` | GET    | Get menu items for a specific location with hierarchical structure |

## Feature Endpoints

### Hero Section

| Endpoint                            | Method | Description                               |
| ----------------------------------- | ------ | ----------------------------------------- |
| `/wp-json/steget/v1/hero/{page_id}` | GET    | Get hero section data for a specific page |

### Selling Points

| Endpoint                                      | Method | Description                                 |
| --------------------------------------------- | ------ | ------------------------------------------- |
| `/wp-json/steget/v1/selling-points/{page_id}` | GET    | Get selling points data for a specific page |

### Testimonials

| Endpoint                          | Method | Description           |
| --------------------------------- | ------ | --------------------- |
| `/wp-json/steget/v1/testimonials` | GET    | Get testimonials data |

### Call to Action (CTA)

| Endpoint                           | Method | Description                              |
| ---------------------------------- | ------ | ---------------------------------------- |
| `/wp-json/steget/v1/cta/{page_id}` | GET    | Get CTA section data for a specific page |

### Posts

| Endpoint                            | Method | Description             |
| ----------------------------------- | ------ | ----------------------- |
| `/wp-json/steget/v1/featured-posts` | GET    | Get featured posts data |

## Module Endpoints

| Endpoint                          | Method | Description                             |
| --------------------------------- | ------ | --------------------------------------- |
| `/wp-json/steget/v1/modules`      | GET    | Get all modules (with optional filters) |
| `/wp-json/steget/v1/modules/{id}` | GET    | Get a specific module by ID             |

### Module Filters

The `/wp-json/steget/v1/modules` endpoint accepts the following query parameters:

- `template`: Filter by module template (e.g., hero, selling_points, testimonials)
- `category`: Filter by module category slug
- `placement`: Filter by module placement slug
- `per_page`: Number of items per page (default: 10)
- `page`: Page number (default: 1)

## Contact Form 7 Endpoints

| Endpoint                             | Method | Description                                       |
| ------------------------------------ | ------ | ------------------------------------------------- |
| `/wp-json/steget/v1/cf7/forms`       | GET    | List all available Contact Form 7 forms           |
| `/wp-json/steget/v1/cf7/form/{id}`   | GET    | Get form structure and fields for a specific form |
| `/wp-json/steget/v1/cf7/submit/{id}` | POST   | Submit a form                                     |

## Homepage Data Endpoints

| Endpoint                              | Method | Description                         |
| ------------------------------------- | ------ | ----------------------------------- |
| `/wp-json/startpage/v1/homepage-data` | GET    | Get all homepage data (deprecated)  |
| `/wp-json/startpage/v2/homepage-data` | GET    | Get all homepage data (new version) |

## Standard WordPress REST API Endpoints

These are part of the core WordPress REST API but are relevant to the headless setup:

| Endpoint                    | Method | Description               |
| --------------------------- | ------ | ------------------------- |
| `/wp-json/wp/v2/pages`      | GET    | Get all pages             |
| `/wp-json/wp/v2/pages/{id}` | GET    | Get a specific page by ID |
| `/wp-json/wp/v2/posts`      | GET    | Get all posts             |
| `/wp-json/wp/v2/posts/{id}` | GET    | Get a specific post by ID |

## Custom Post Type Endpoints

| Endpoint                          | Method | Description                       |
| --------------------------------- | ------ | --------------------------------- |
| `/wp-json/wp/v2/portfolio`        | GET    | Get all portfolio items           |
| `/wp-json/wp/v2/testimonial`      | GET    | Get all testimonials              |
| `/wp-json/wp/v2/module`           | GET    | Get all modules                   |
| `/wp-json/evaluation/v1/save`     | POST   | Save student evaluation data      |
| `/wp-json/evaluation/v1/get/{id}` | GET    | Get student evaluation data by ID |

## Example API Requests

### Fetching Menu Items

```javascript
fetch("/wp-json/steget/v1/menu/primary")
  .then((response) => response.json())
  .then((data) => console.log(data));
```

### Submitting a Contact Form

```javascript
const formData = new URLSearchParams();
formData.append("your-name", "John Doe");
formData.append("your-email", "john@example.com");
formData.append("your-subject", "Hello");
formData.append("your-message", "This is a test message.");

fetch("/wp-json/steget/v1/cf7/submit/146", {
  method: "POST",
  headers: {
    "Content-Type": "application/x-www-form-urlencoded",
  },
  body: formData.toString(),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

### Fetching Homepage Data

```javascript
fetch("/wp-json/startpage/v2/homepage-data")
  .then((response) => response.json())
  .then((data) => {
    console.log("Hero:", data.hero);
    console.log("Featured Posts:", data.featured_posts);
    console.log("CTA:", data.cta);
    console.log("Testimonials:", data.testimonials);
  });
```

### Fetching Modules

```javascript
// Get hero modules
fetch("/wp-json/steget/v1/modules?template=hero")
  .then((response) => response.json())
  .then((data) => console.log(data));

// Get modules for a specific placement
fetch("/wp-json/steget/v1/modules?placement=homepage")
  .then((response) => response.json())
  .then((data) => console.log(data));
```

## API Response Format Examples

### Hero Endpoint Response

```json
{
  "title": "Welcome to Our Site",
  "intro": "Lorem ipsum dolor sit amet",
  "image": "https://example.com/wp-content/uploads/hero.jpg",
  "buttons": [
    {
      "text": "Learn More",
      "url": "/about-us",
      "style": "primary"
    }
  ]
}
```

### Testimonials Endpoint Response

```json
[
  {
    "id": 123,
    "content": "Great service!",
    "author_name": "John Doe",
    "author_position": "CEO, Example Inc.",
    "author_image": "https://example.com/wp-content/uploads/john.jpg"
  },
  {
    "id": 124,
    "content": "Highly recommended!",
    "author_name": "Jane Smith",
    "author_position": "Marketing Director",
    "author_image": "https://example.com/wp-content/uploads/jane.jpg"
  }
]
```

### Module Endpoint Response

```json
{
  "id": 456,
  "title": "Our Services",
  "content": "<p>We offer a range of services...</p>",
  "template": "selling_points",
  "layout": "center",
  "full_width": false,
  "background_color": "#f5f5f5",
  "selling_points": [
    {
      "title": "Service 1",
      "description": "Description of service 1",
      "icon": "icon-service-1"
    },
    {
      "title": "Service 2",
      "description": "Description of service 2",
      "icon": "icon-service-2"
    }
  ]
}
```

# Contact Form 7 Integration

Integrate Contact Form 7 with a headless WordPress setup, including API endpoints, implementation examples, and troubleshooting tips.

## Overview

Contact Form 7 (CF7) is a WordPress plugin for creating forms and send email, but it doesn't natively support headless implementations (as I know of). The custom endpoints and components in this theme bridge this gap, allowing you to use CF7 forms in a decoupled environment.

## API Endpoints

You have the following custom endpoints for Contact Form 7:

| Endpoint                             | Method | Description                                       |
| ------------------------------------ | ------ | ------------------------------------------------- |
| `/wp-json/steget/v1/cf7/forms`       | GET    | List all available Contact Form 7 forms           |
| `/wp-json/steget/v1/cf7/form/{id}`   | GET    | Get form structure and fields for a specific form |
| `/wp-json/steget/v1/cf7/submit/{id}` | POST   | Submit a form                                     |

## How to Use the API

### 1. List Available Forms

First, fetch all available forms to get their IDs:

```javascript
fetch("/wp-json/steget/v1/cf7/forms")
  .then((response) => response.json())
  .then((forms) => {
    console.log("Available forms:", forms);
    // forms = [{ id: 123, title: "Contact Form", shortcode: "[contact-form-7 id=\"123\" title=\"Contact Form\"]" }, ...]
  });
```

### 2. Get Form Structure

To build a dynamic form, fetch its structure:

```javascript
const formId = 146; // Replace with your form ID

fetch(`/wp-json/steget/v1/cf7/form/${formId}`)
  .then((response) => response.json())
  .then((formData) => {
    console.log("Form structure:", formData);
    // formData includes title, fields, and messages
  });
```

### 3. Submit Form Data

To submit the form, send a POST request with the form data:

```javascript
const formId = 146; // Replace with your form ID
const formData = new URLSearchParams();

// Add form fields
formData.append("your-name", "John Doe");
formData.append("your-email", "john@example.com");
formData.append("your-subject", "Hello");
formData.append("your-message", "This is a test message.");

// Submit the form
fetch(`/wp-json/steget/v1/cf7/submit/${formId}`, {
  method: "POST",
  headers: {
    "Content-Type": "application/x-www-form-urlencoded",
  },
  body: formData.toString(),
})
  .then((response) => response.json())
  .then((result) => {
    console.log("Submission result:", result);
    // result = { status: "mail_sent", message: "Thank you for your message..." }
  })
  .catch((error) => {
    console.error("Error:", error);
  });
```

## Important Notes About Submission

1. **Content-Type**: Always use `application/x-www-form-urlencoded` for the content type. This is what CF7 expects.

2. **Field Names**: Use the exact field names defined in your CF7 form. These typically follow the pattern `your-name`, `your-email`, etc.

3. **Response Format**: The submission endpoint returns a JSON object with:
   - `status`: Either "mail_sent" (success) or "mail_failed" (error)
   - `message`: The success/error message configured in the CF7 form settings
   - `errors`: (If validation fails) Details about which fields failed validation

## React Integration

Here's how to create a React component that integrates with the CF7 API:

```jsx
import { useState } from "react";

const ContactForm = ({ formId, apiUrl = "/wp-json" }) => {
  const [formData, setFormData] = useState({
    "your-name": "",
    "your-email": "",
    "your-subject": "",
    "your-message": "",
  });

  const [formStatus, setFormStatus] = useState({
    submitting: false,
    submitted: false,
    success: false,
    message: "",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setFormStatus({
      submitting: true,
      submitted: false,
      success: false,
      message: "",
    });

    try {
      // Create URL encoded form data
      const urlEncodedData = new URLSearchParams();
      Object.entries(formData).forEach(([key, value]) => {
        urlEncodedData.append(key, value);
      });

      // Submit the form
      const response = await fetch(`${apiUrl}/steget/v1/cf7/submit/${formId}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: urlEncodedData.toString(),
      });

      const result = await response.json();

      if (result.status === "mail_sent") {
        setFormStatus({
          submitting: false,
          submitted: true,
          success: true,
          message: result.message,
        });

        // Reset form on success
        setFormData({
          "your-name": "",
          "your-email": "",
          "your-subject": "",
          "your-message": "",
        });
      } else {
        setFormStatus({
          submitting: false,
          submitted: true,
          success: false,
          message: result.message,
        });
      }
    } catch (error) {
      console.error("Form submission error:", error);
      setFormStatus({
        submitting: false,
        submitted: true,
        success: false,
        message:
          "There was an error sending your message. Please try again later.",
      });
    }
  };

  return (
    <div className="contact-form-wrapper">
      {formStatus.submitted && (
        <div
          className={`form-message ${formStatus.success ? "success" : "error"}`}
        >
          {formStatus.message}
        </div>
      )}

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="your-name">Your Name</label>
          <input
            type="text"
            id="your-name"
            name="your-name"
            value={formData["your-name"]}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="your-email">Your Email</label>
          <input
            type="email"
            id="your-email"
            name="your-email"
            value={formData["your-email"]}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="your-subject">Subject</label>
          <input
            type="text"
            id="your-subject"
            name="your-subject"
            value={formData["your-subject"]}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="your-message">Your Message</label>
          <textarea
            id="your-message"
            name="your-message"
            value={formData["your-message"]}
            onChange={handleChange}
            rows="5"
            required
          ></textarea>
        </div>

        <button
          type="submit"
          className="submit-button"
          disabled={formStatus.submitting}
        >
          {formStatus.submitting ? "Sending..." : "Send Message"}
        </button>
      </form>
    </div>
  );
};

export default ContactForm;
