// src/state.rs
use chrono::{DateTime, Utc};
use serde::Serialize;
use sqlx::{postgres::PgPoolOptions, PgPool};

use crate::config::AppConfig;

#[derive(Clone)]
pub struct AppState {
    pub pool: PgPool,
    pub nasa_url: String,
    pub nasa_key: String,
    pub fallback_url: String,
    pub every_osdr: u64,
    pub every_iss: u64,
    pub every_apod: u64,
    pub every_neo: u64,
    pub every_donki: u64,
    pub every_spacex: u64,
}

#[derive(Serialize)]
pub struct Health {
    pub status: &'static str,
    pub now: DateTime<Utc>,
}

pub async fn build_state(cfg: AppConfig) -> anyhow::Result<AppState> {
    let pool = PgPoolOptions::new()
        .max_connections(5)
        .connect(&cfg.db_url)
        .await?;

    init_db(&pool).await?;

    Ok(AppState {
        pool,
        nasa_url: cfg.nasa_url,
        nasa_key: cfg.nasa_key,
        fallback_url: cfg.fallback_url,
        every_osdr: cfg.every_osdr,
        every_iss: cfg.every_iss,
        every_apod: cfg.every_apod,
        every_neo: cfg.every_neo,
        every_donki: cfg.every_donki,
        every_spacex: cfg.every_spacex,
    })
}

/* ---------- DB boot ---------- */
pub async fn init_db(pool: &PgPool) -> anyhow::Result<()> {
    // ISS
    sqlx::query(
        "CREATE TABLE IF NOT EXISTS iss_fetch_log(
            id BIGSERIAL PRIMARY KEY,
            fetched_at TIMESTAMPTZ NOT NULL DEFAULT now(),
            source_url TEXT NOT NULL,
            payload JSONB NOT NULL
        )",
    )
    .execute(pool)
    .await?;

    // OSDR
    sqlx::query(
        "CREATE TABLE IF NOT EXISTS osdr_items(
            id BIGSERIAL PRIMARY KEY,
            dataset_id TEXT,
            title TEXT,
            status TEXT,
            updated_at TIMESTAMPTZ,
            inserted_at TIMESTAMPTZ NOT NULL DEFAULT now(),
            raw JSONB NOT NULL
        )",
    )
    .execute(pool)
    .await?;

    sqlx::query(
        "CREATE UNIQUE INDEX IF NOT EXISTS ux_osdr_dataset_id
         ON osdr_items(dataset_id) WHERE dataset_id IS NOT NULL",
    )
    .execute(pool)
    .await?;

    // универсальный кэш космоданных
    sqlx::query(
        "CREATE TABLE IF NOT EXISTS space_cache(
            id BIGSERIAL PRIMARY KEY,
            source TEXT NOT NULL,
            fetched_at TIMESTAMPTZ NOT NULL DEFAULT now(),
            payload JSONB NOT NULL
        )",
    )
    .execute(pool)
    .await?;

    sqlx::query(
        "CREATE INDEX IF NOT EXISTS ix_space_cache_source
         ON space_cache(source,fetched_at DESC)",
    )
    .execute(pool)
    .await?;

    Ok(())
}
