import { defineConfig } from 'vitepress'

export default defineConfig({
  title: "Dashboard Starter Kit",
  description: "Census and survey dashboard starter kit documentation",
  base: '/dashboard-starter-kit/',
  ignoreDeadLinks: [
    /^https?:\/\/localhost/
  ],

  themeConfig: {
    search: {
      provider: 'local'
    },

    nav: [
      { text: 'Home', link: '/' },
      { text: 'Users', link: '/user/introduction' },
      { text: 'Managers', link: '/manager/introduction' },
      { text: 'Developers', link: '/developer/introduction' },
    ],

    sidebar: {
      '/user/': [
        { text: 'Introduction', link: '/user/introduction' },
        {
          text: 'User Interface',
          collapsed: true,
          items: [
            { text: 'Localization', link: '/user/user-interface/localization' },
            { text: 'Navigation', link: '/user/user-interface/navigation' },
            { text: 'Page descriptions', link: '/user/user-interface/page-descriptions' },
          ]
        },
        {
          text: 'Dashboard components',
          collapsed: true,
          items: [
            { text: 'Homepage', link: '/user/dashboard-components/homepage' },
            { text: 'Charts', link: '/user/dashboard-components/charts' },
            { text: 'Maps', link: '/user/dashboard-components/maps' },
            { text: 'Reports', link: '/user/dashboard-components/reports' },
            { text: 'Area Insights', link: '/user/dashboard-components/area-insights' },
            { text: 'User profile', link: '/user/dashboard-components/user-profile' },
          ]
        },
        {
          text: 'Advanced',
          collapsed: true,
          items: [
            { text: '2FA', link: '/user/advanced/2fa' },
            { text: 'Browser sessions', link: '/user/advanced/browser-sessions' },
          ]
        },
      ],

      '/manager/': [
        { text: 'Introduction', link: '/manager/introduction' },
        {
          text: 'Access Control',
          collapsed: true,
          items: [
            { text: 'Users', link: '/manager/access-control/users' },
            { text: 'Roles', link: '/manager/access-control/roles' },
          ]
        },
        {
          text: 'Core Configuration',
          collapsed: true,
          items: [
            { text: 'Data Sources', link: '/manager/core-configuration/data-sources' },
            { text: 'Area Hierarchy', link: '/manager/core-configuration/area-hierarchy' },
            { text: 'Areas', link: '/manager/core-configuration/areas' },
            { text: 'Reference Values', link: '/manager/core-configuration/reference-values' },
          ]
        },
        {
          text: 'Dashboard Artefacts',
          collapsed: true,
          items: [
            { text: 'Pages', link: '/manager/dashboard-arefacts/pages' },
            { text: 'Indicators', link: '/manager/dashboard-arefacts/indicators' },
            { text: 'Scorecards', link: '/manager/dashboard-arefacts/scorecards' },
            { text: 'Gauges', link: '/manager/dashboard-arefacts/gauges' },
            { text: 'Reports', link: '/manager/dashboard-arefacts/reports' },
            { text: 'Map Indicators', link: '/manager/dashboard-arefacts/map-indicators' },
          ]
        },
        { text: 'Announcements', link: '/manager/announcements' },
        { text: 'Usage stats', link: '/manager/usage-stats' },
        { text: 'Query Analytics', link: '/manager/query-analytics' },
        { text: 'Settings', link: '/manager/settings' },
      ],

      '/developer/': [
        { text: 'Introduction', link: '/developer/introduction' },
        {
          text: 'Getting started',
          collapsed: true,
          items: [
            { text: 'Introduction', link: '/developer/getting-started/introduction' },
            { text: 'Requirements', link: '/developer/getting-started/requirements' },
            { text: 'Environment setup', link: '/developer/getting-started/environment-setup' },
            { text: 'Installation', link: '/developer/getting-started/installation' },
            { text: 'Configuration', link: '/developer/getting-started/configuration' },
            { text: 'Next steps', link: '/developer/getting-started/next-steps' },
          ]
        },
        {
          text: 'Foundation',
          collapsed: true,
          items: [
            { text: 'Core concepts', link: '/developer/foundation/core-concepts' },
            { text: 'Folder structure', link: '/developer/foundation/folder-structure' },
            { text: 'Core configuration', link: '/developer/foundation/core-configuration' },
            { text: 'Understanding CSPro data', link: '/developer/foundation/understanding-cspro-data' },
            { text: 'Breakout query builder', link: '/developer/foundation/breakout-query-builder' },
            { text: 'Query fragments', link: '/developer/foundation/query-fragments' },
          ]
        },
        {
          text: 'Building your dashboard',
          collapsed: true,
          items: [
            { text: 'Creating scorecards', link: '/developer/building-your-dashboard/creating-scorecards' },
            { text: 'Creating gauges', link: '/developer/building-your-dashboard/creating-gauges' },
            { text: 'Overriding case stats', link: '/developer/building-your-dashboard/overriding-case-stats' },
            { text: 'Creating indicators', link: '/developer/building-your-dashboard/creating-indicators' },
            { text: 'Hierarchical compatibility', link: '/developer/building-your-dashboard/hierarchial-compatibility' },
            { text: 'Creating reports', link: '/developer/building-your-dashboard/creating-reports' },
            { text: 'Creating map based indicators', link: '/developer/building-your-dashboard/creating-map-based-indicators' },
            { text: 'Creating reference value synthesizers', link: '/developer/building-your-dashboard/creating-reference-value-synthesizers' },
            { text: 'Artefact organization', link: '/developer/building-your-dashboard/artefact-organization' },
            { text: 'Area filter', link: '/developer/building-your-dashboard/area-filter' },
            { text: 'Area insights', link: '/developer/building-your-dashboard/area-insights' },
            { text: 'Customizing the look and feel', link: '/developer/building-your-dashboard/customizing-the-look-and-feel' },
            { text: 'MCP server', link: '/developer/building-your-dashboard/mcp-server' },
          ]
        },
        {
          text: 'Deployment guide',
          collapsed: true,
          items: [
            { text: 'Configuring email server', link: '/developer/deployment-guide/configuring-email-server' },
            { text: 'Deploy using docker', link: '/developer/deployment-guide/deploy-using-docker' },
            { text: 'Migrating data', link: '/developer/deployment-guide/migrating-data' },
            { text: 'Running queue workers', link: '/developer/deployment-guide/running-queue-workers' },
            { text: 'Linking storage directory', link: '/developer/deployment-guide/linking-storage-directory' },
            { text: 'Production checklist', link: '/developer/deployment-guide/production-checklist' },
          ]
        },
        {
          text: 'Migration guides',
          collapsed: true,
          items: [
            { text: 'update command', link: '/developer/migration-guides/update-command' },
            { text: 'Upgrading to v5', link: '/developer/migration-guides/upgrading-to-v5' },
          ]
        },
        {
          text: 'Advanced topics',
          collapsed: true,
          items: [
            { text: 'Under the hood', link: '/developer/advanced-topics/under-the-hood' },
            { text: 'Developer mode', link: '/developer/advanced-topics/developer-mode' },
            { text: 'Caching', link: '/developer/advanced-topics/caching' },
            { text: 'Localization', link: '/developer/advanced-topics/localization' },
            { text: 'Color palettes', link: '/developer/advanced-topics/color-palettes' },
            { text: 'Security', link: '/developer/advanced-topics/security' },
            { text: 'Performance tuning', link: '/developer/advanced-topics/performance-tuning' },
            { text: 'Area restrictions', link: '/developer/advanced-topics/area-restrictions' },
          ]
        },
        {
          text: 'Support',
          collapsed: true,
          items: [
            { text: 'Getting help', link: '/developer/support/getting-help' },
            { text: 'Troubleshooting', link: '/developer/support/troubleshooting' },
          ]
        },
        { text: 'Contributing', link: '/developer/contributing' },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/tech-acs/dashboard-starter-kit' }
    ]
  }
})
