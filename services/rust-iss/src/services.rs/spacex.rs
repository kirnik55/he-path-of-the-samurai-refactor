// src/services/spacex.rs
use std::time::Duration;

use serde_json::Value;

use crate::state::AppState;
use crate::utils::write_cache;

pub async fn fetch_spacex_next(st: &AppState) -> anyhow::Result<()> {
    let url = "https://api.spacexdata.com/v4/launches/next";
    let client = reqwest::Client::builder()
        .timeout(Duration::from_secs(30))
        .build()?;
    let json: Value = client.get(url).send().await?.json().await?;
    write_cache(&st.pool, "spacex", json).await
}
