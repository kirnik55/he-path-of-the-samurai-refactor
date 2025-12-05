// src/routes/health.rs
use axum::Json;
use chrono::Utc;

use crate::state::Health;

pub async fn health() -> Json<Health> {
    Json(Health {
        status: "ok",
        now: Utc::now(),
    })
}
