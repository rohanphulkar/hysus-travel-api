import SwaggerUI from "swagger-ui";
import "swagger-ui/dist/swagger-ui.css";
SwaggerUI({
    dom_id: "#swagger-api",
    url:
        window.location.protocol +
        "//" +
        window.location.host +
        "/swagger/api.yaml",
});
