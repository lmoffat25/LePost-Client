# LePost Client - User Guide

## Welcome to LePost Client 2.0

LePost Client has been completely redesigned with a focus on simplicity, performance, and user experience. This guide will help you get started with the new interface and make the most of its features.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Managing Ideas](#managing-ideas)
4. [Generating Articles](#generating-articles)
5. [Settings Configuration](#settings-configuration)
6. [Tips & Best Practices](#tips--best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Getting Started

### System Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- Active LePost API account and key

### Installation
1. Upload the plugin files to your WordPress installation
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **LePost → Settings** to configure your API key

### Initial Setup
1. **Configure API Key:** Go to Settings → API tab and enter your LePost API key
2. **Test Connection:** Click "Test Connection" to verify your API key works
3. **Review Settings:** Configure content and generation settings to your preferences
4. **Add Your First Idea:** Navigate to Ideas and create your first content idea

---

## Dashboard Overview

The dashboard provides a central overview of your LePost activity and quick access to key features.

### Statistics Cards
- **Ideas:** Total number of content ideas in your library
- **Articles:** Generated articles count
- **Posts:** WordPress posts created from articles
- **API Credits:** Remaining credits in your account

### Quick Actions
- **Add New Idea:** Quickly create a new content idea
- **Generate Articles:** Access the article generation workflow
- **Import Ideas:** Bulk import ideas from CSV file
- **Configure API:** Access API settings

### Recent Activity
- **Recent Ideas:** Latest ideas added to your library
- **Recent Articles:** Recently generated articles
- Quick access to edit or manage items

### API Status
Real-time display of your API connection status and available credits.

---

## Managing Ideas

Ideas are the foundation of your content strategy. The Ideas section allows you to create, organize, and manage your content concepts.

### Adding Ideas

#### Manual Entry
1. Navigate to **LePost → Ideas**
2. Click **"Add New Idea"**
3. Fill in the idea details:
   - **Title:** Clear, descriptive title for your idea
   - **Description:** Detailed description of the content concept
   - **Keywords:** Relevant keywords (comma-separated)
   - **Category:** Content category for organization
4. Click **"Save Idea"**

#### Bulk Import
1. Click **"Import Ideas"** on the Ideas page
2. Upload a CSV file with columns: title, description, keywords, category
3. Review the import preview
4. Confirm the import

### Organizing Ideas
- **Search:** Use the search box to find specific ideas
- **Filter:** Filter by category, status, or date
- **Sort:** Click column headers to sort ideas
- **Bulk Actions:** Select multiple ideas for bulk operations

### Idea Status
- **Draft:** New ideas ready for development
- **In Progress:** Ideas currently being processed
- **Generated:** Ideas that have been converted to articles
- **Published:** Articles that have been published as posts

---

## Generating Articles

Convert your ideas into high-quality articles using the LePost AI engine.

### Single Article Generation

1. **From Ideas List:**
   - Navigate to **LePost → Ideas**
   - Click **"Generate Article"** next to any idea
   - Review the generation settings
   - Click **"Generate Article"**

2. **From Generate Page:**
   - Navigate to **LePost → Generate Articles**
   - Select ideas from the list
   - Configure generation options
   - Click **"Generate Selected Articles"**

### Bulk Article Generation

1. Go to **LePost → Generate Articles**
2. Select multiple ideas using checkboxes
3. Configure global generation settings:
   - **Content Length:** Short, Medium, or Long
   - **Writing Style:** Professional, Casual, or Technical
   - **Include Images:** Auto-generate relevant images
4. Click **"Generate Selected Articles"**

### Generation Settings

#### Content Options
- **Length:** Control article word count
- **Style:** Adjust writing tone and approach
- **Structure:** Choose article format (blog post, guide, list, etc.)
- **SEO Optimization:** Include SEO-friendly elements

#### WordPress Integration
- **Post Status:** Draft, Published, or Scheduled
- **Category:** Auto-assign WordPress categories
- **Tags:** Generate relevant post tags
- **Featured Image:** Auto-set featured images

### Managing Generated Articles

Generated articles appear in the WordPress Posts section and can be:
- Edited using the WordPress editor
- Scheduled for publication
- Organized with categories and tags
- Enhanced with additional content

---

## Settings Configuration

Configure LePost Client to match your workflow and preferences.

### API Settings

#### API Key Configuration
1. Navigate to **LePost → Settings → API**
2. Enter your LePost API key
3. Click **"Test Connection"** to verify
4. Save settings

#### Connection Status
- **Green:** API connection active and working
- **Yellow:** Connection issues detected
- **Red:** API key invalid or connection failed

### General Settings

#### Content Preferences
- **Default Language:** Primary language for content generation
- **Content Quality:** Balance between speed and quality
- **Auto-Save:** Automatically save drafts while working

#### User Interface
- **Items per Page:** Number of ideas shown per page
- **Default View:** List or grid view for ideas
- **Theme Integration:** Match WordPress admin theme

### Content Settings

#### Article Defaults
- **Default Length:** Standard article length
- **Writing Style:** Preferred writing tone
- **SEO Settings:** Default SEO optimization level
- **Image Settings:** Auto-image generation preferences

#### WordPress Integration
- **Default Post Status:** Auto-set status for generated posts
- **Default Category:** Fallback category for new posts
- **Author Assignment:** Default post author
- **Publication Schedule:** Auto-scheduling options

### Generation Settings

#### AI Parameters
- **Creativity Level:** Control AI creativity vs. accuracy
- **Research Depth:** Amount of background research
- **Fact Checking:** Verification level for generated content
- **Citation Style:** Academic, journalistic, or casual

#### Quality Control
- **Review Required:** Require manual review before publishing
- **Plagiarism Check:** Check content originality
- **Grammar Check:** Auto-correct grammar and style
- **Word Count Limits:** Minimum and maximum word counts

---

## Tips & Best Practices

### Creating Effective Ideas

#### Be Specific
- Use clear, descriptive titles
- Provide detailed descriptions
- Include relevant keywords

#### Organize Strategically
- Use consistent category naming
- Group related ideas together
- Plan content series and sequences

#### Optimize for Generation
- Include target audience information
- Specify desired tone and style
- Provide context and background

### Maximizing Article Quality

#### Review Generation Settings
- Choose appropriate content length
- Select matching writing style
- Configure SEO optimization

#### Post-Generation Enhancement
- Review and edit generated content
- Add personal insights and examples
- Verify facts and citations
- Optimize for your audience

#### WordPress Integration
- Use appropriate categories and tags
- Set proper publication schedules
- Configure featured images
- Optimize for SEO

### Workflow Optimization

#### Batch Processing
- Create multiple ideas in batches
- Generate articles in groups
- Schedule publications strategically

#### Quality Control
- Establish review workflows
- Use draft status for review
- Implement approval processes

#### Performance Monitoring
- Track article performance
- Monitor API credit usage
- Analyze content effectiveness

---

## Troubleshooting

### Common Issues

#### API Connection Problems

**Symptom:** "Connection Failed" error
**Solutions:**
1. Verify your API key is correct
2. Check your internet connection
3. Ensure your hosting provider allows external API calls
4. Contact LePost support if issues persist

**Symptom:** "Invalid API Key" error
**Solutions:**
1. Double-check your API key for typos
2. Ensure your LePost account is active
3. Regenerate your API key from the LePost dashboard
4. Clear any cached settings

#### Article Generation Issues

**Symptom:** Generation takes too long
**Solutions:**
1. Check your API credit balance
2. Reduce content length settings
3. Simplify idea descriptions
4. Contact support for server status

**Symptom:** Poor article quality
**Solutions:**
1. Provide more detailed idea descriptions
2. Adjust creativity and quality settings
3. Use more specific keywords
4. Review and refine generated content

#### WordPress Integration Problems

**Symptom:** Articles not appearing in WordPress
**Solutions:**
1. Check user permissions
2. Verify post status settings
3. Review category and tag assignments
4. Check for plugin conflicts

### Getting Help

#### Documentation
- Review this user guide
- Check the FAQ section
- Browse video tutorials

#### Support Channels
- Email: support@lepost.ai
- Discord: LePost Community
- Knowledge Base: help.lepost.ai

#### Community Resources
- WordPress.org plugin page
- GitHub repository
- User community forums

---

## Advanced Features

### Custom Workflows

#### Content Series
Create connected articles by:
1. Planning related ideas in sequence
2. Using consistent keywords and themes
3. Cross-referencing articles
4. Building topic clusters

#### Editorial Calendar
Integrate with your publishing schedule:
1. Plan ideas around key dates
2. Schedule generation and publication
3. Coordinate with marketing campaigns
4. Balance content types and topics

### API Integration

#### Webhook Support
Configure webhooks for:
- Generation completion notifications
- API quota alerts
- Status change updates
- Error notifications

#### Custom Integrations
Advanced users can:
- Use REST API endpoints
- Build custom workflows
- Integrate with external tools
- Automate content processes

### Performance Optimization

#### Credit Management
- Monitor credit usage patterns
- Optimize generation settings
- Plan content production cycles
- Set up usage alerts

#### System Performance
- Configure caching appropriately
- Optimize database queries
- Monitor server resources
- Regular maintenance tasks

---

## Version History

### Version 2.0 - Major Interface Redesign
- Complete interface simplification
- Improved performance and reliability
- Enhanced WordPress integration
- Better mobile responsiveness
- Streamlined workflows

### Migration from v1.x
If upgrading from LePost Client 1.x:
1. Your existing ideas and settings are preserved
2. The interface has been simplified
3. New features are available in Settings
4. Review the updated workflow in this guide

---

## Support & Feedback

We're committed to making LePost Client the best AI content generation tool for WordPress. Your feedback helps us improve!

### Contact Information
- **Email:** support@lepost.ai
- **Website:** https://lepost.ai
- **Documentation:** https://docs.lepost.ai

### Feature Requests
Submit feature requests through:
- GitHub issues
- Support email
- Community forums

### Bug Reports
When reporting bugs, please include:
- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Error messages
- Screenshots if applicable

---

Thank you for using LePost Client! We hope this guide helps you create amazing content with AI assistance. 