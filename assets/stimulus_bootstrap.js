import { startStimulusApp, registerControllers } from "vite-plugin-symfony/stimulus/helpers";

const app = startStimulusApp();

// startStimulusApp() loads controllers.json (Turbo, UX) via the plugin's
// virtual:symfony/controllers module. Use a LAZY glob here (no `eager: true`):
// this helper version registers controllers from function-valued modules only;
// eager namespaces are silently skipped, so nothing would connect.
registerControllers(
    app,
    import.meta.glob("./controllers/*_controller.js")
);

export { app };
