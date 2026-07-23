import { defineConfig } from "cypress";
import { execSync } from "node:child_process";

export default defineConfig({
    e2e: {
        baseUrl: "http://127.0.0.1:8098",
        specPattern: "cypress/e2e/**/*.cy.js",
        supportFile: false,
        async setupNodeEvents(on, config) {
            on("task", {
                // Resets the test database to a known seeded state.
                // Runs before each spec via beforeEach().
                seedDb() {
                    const env = { ...process.env, APP_ENV: "test" };
                    execSync("php bin/console doctrine:database:create --if-not-exists -n", { stdio: "pipe", env });
                    execSync("php bin/console doctrine:schema:update --force -n", { stdio: "pipe", env });
                    execSync("php bin/console doctrine:fixtures:load -n", { stdio: "pipe", env });
                    return null;
                },
            });
            return config;
        },
    },
});
