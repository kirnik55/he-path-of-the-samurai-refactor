// src/routes/mod.rs
use axum::{routing::get, Router};
use crate::state::AppState;

pub mod health;
pub mod iss;
pub mod osdr;
pub mod space;

pub fn build_router(state: AppState) -> Router {
    Router::new()
        .route("/health", get(health::health))
        // ISS
        .route("/last", get(iss::last_iss))
        .route("/fetch", get(iss::trigger_iss))
        .route("/iss/trend", get(iss::iss_trend))
        // OSDR
        .route("/osdr/sync", get(osdr::osdr_sync))
        .route("/osdr/list", get(osdr::osdr_list))
        // space_cache
        .route("/space/:src/latest", get(space::space_latest))
        .route("/space/refresh", get(space::space_refresh))
        .route("/space/summary", get(space::space_summary))
        .with_state(state)
}
