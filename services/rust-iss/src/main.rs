// src/main.rs
mod config;
mod state;
mod utils;
mod routes;
mod services;
mod tasks;

use tracing_subscriber::{EnvFilter, FmtSubscriber};
use tracing::info;

use crate::config::load_config;
use crate::state::build_state;
use crate::routes::build_router;
use crate::tasks::spawn_background_jobs;

#[tokio::main]
async fn main() -> anyhow::Result<()> {
    // логирование
    let subscriber = FmtSubscriber::builder()
        .with_env_filter(EnvFilter::from_default_env())
        .finish();
    let _ = tracing::subscriber::set_global_default(subscriber);

    // конфиг + состояние
    let cfg = load_config();
    let state = build_state(cfg).await?;

    // фоновые задачи
    spawn_background_jobs(&state);

    // HTTP
    let app = build_router(state);

    let listener = tokio::net::TcpListener::bind(("0.0.0.0", 3000)).await?;
    info!("rust_iss listening on 0.0.0.0:3000");
    axum::serve(listener, app.into_make_service()).await?;
    Ok(())
}
