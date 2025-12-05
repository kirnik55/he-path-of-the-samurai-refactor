// src/services/apod.rs
use std::time::Duration;

use serde_json::Value;

use crate::state::AppState;
use crate::utils::write_cache;

pub async fn fetch_apod(st: &AppState) -> anyhow::Result<()> {
    let url = "https://api.nasa.gov/planetary/apod";
    let client = reqwest::Client::builder()
        .timeout(Duration::from_secs(30))
        .build()?;
    let mut req = client.get(url).query(&[("thumbs", "true")]);
    if !st.nasa_key.is_empty() {
        req = req.query(&[("api_key", &st.nasa_key)]);
    }
    let json: Value = req.send().await?.json().await?;
    write_cache(&st.pool, "apod", json).await
}
