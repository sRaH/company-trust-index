describe("Review expand/collapse", () => {
    beforeEach(() => {
        cy.task("seedDb");
    });

    it("reveals the full text of a long review only after expanding", () => {
        const longText = (
            "Ez egy szándékosan hosszú értékelés, amely több bekezdésnyi " +
            "részletet tartalmaz a szolgáltatás minőségéről, a kommunikációról, " +
            "a szállítási időről és az ügyfélszolgálatról, hogy a kártyán " +
            "látható előnézet túlcsorduljon, és megjelenjen a kibontás gomb. "
        ).repeat(4).trim();

        cy.visit("/review/new");
        cy.get('input[name="review[companyName]"]').type("HosszuVelemeny Kft");
        cy.get('[data-star-value="5"]').click();
        cy.get('textarea[name="review[reviewText]"]').type(longText);
        cy.get('input[name="review[authorEmail]"]').type("long@example.com");
        cy.get('button[type="submit"]').click();

        cy.url().should("eq", Cypress.config("baseUrl") + "/");

        // Newest review sorts first on the homepage.
        cy.get(".review-card").first().as("card");
        cy.get("@card").find(".review-card__title").should("contain", "HosszuVelemeny Kft");

        // The full text is always in the DOM...
        cy.get("@card").find(".review-card__text").should("contain", longText);
        // ...and because it overflows the clamp, the toggle is revealed.
        cy.get("@card")
            .find(".review-card__expand")
            .should("be.visible")
            .and("have.attr", "aria-expanded", "false")
            .and("contain", "Bővebben");

        // Expanding flips the state and the label.
        cy.get("@card").find(".review-card__expand").click();
        cy.get("@card")
            .find(".review-card__expand")
            .should("have.attr", "aria-expanded", "true")
            .and("contain", "Kevesebb");
        cy.get("@card").find(".review-card__text").should("be.visible").and("contain", longText);

        // Collapsing restores the preview state.
        cy.get("@card").find(".review-card__expand").click();
        cy.get("@card")
            .find(".review-card__expand")
            .should("have.attr", "aria-expanded", "false")
            .and("contain", "Bővebben");
    });

    it("does not show a toggle for short seeded reviews", () => {
        cy.visit("/");

        // Short seeded reviews never overflow the clamp, so their toggle stays hidden.
        cy.get(".review-card")
            .first()
            .find(".review-card__expand")
            .should("not.be.visible");
    });

    it("can expand a long review via the keyboard", () => {
        const longText = (
            "Még egy hosszú értékelés, amely billentyűzetes fókusznál is " +
            "kibontható, és az expanded állapot helyesen frissül. "
        ).repeat(5).trim();

        cy.visit("/review/new");
        cy.get('input[name="review[companyName]"]').type("Billentyu Cég");
        cy.get('[data-star-value="4"]').click();
        cy.get('textarea[name="review[reviewText]"]').type(longText);
        cy.get('input[name="review[authorEmail]"]').type("kb@example.com");
        cy.get('button[type="submit"]').click();
        cy.url().should("eq", Cypress.config("baseUrl") + "/");

        cy.get(".review-card").first().as("card");
        cy.get("@card").find(".review-card__expand").as("toggle");

        cy.get("@toggle").should("be.visible");
        cy.get("@toggle").focus().should("be.focused");
        cy.get("@toggle").type("{enter}");
        cy.get("@toggle").should("have.attr", "aria-expanded", "true");
    });
});
