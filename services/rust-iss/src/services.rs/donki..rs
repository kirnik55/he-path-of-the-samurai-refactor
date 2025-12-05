// src/services/donki.rs
use std::time::Duration;

use serde_json::Value;

use crate::state::AppState;
use crate::utils::{last_days, write_cache};

pub async fn fetch_donki(st: &AppState) -> anyhow::Result<()> {
    let _ = fetch_donki_flr(st).await;
    let _ = fetch_donki_cme(st).await;
    Ok(())
}

pub async fn fetch_donki_flr(st: &AppState) -> anyhow::Result<()> {
    let (from, to) = last_days(5);
    let url = "https://api.nasa.gov/DONKI/FLR";
    let client = reqwest::Client::builder()
        .timeout(Duration::from_secs(30))
        .build()?;
    let mut req = client.get(url).query(&[("startDate", from), ("endDate", to)]);
    if !st.nasa_key.is_empty() {
        req = req.query(&[("api_key", &st.nasa_key)]);
    }
    let json: Value = req.send().await?.json().await?;
    write_cache(&st.pool, "flr", json).await
}

pub async fn fetch_donki_cme(st: &AppState) -> anyhow::Result<()> {
    let (from, to) = last_days(5);
    let url = "https://api.nasa.gov/DONKI/CME";
    let client = reqwest::Client::builder()
        .timeout(Duration::from_secs(30))
        .build()?;
    let mut req = client.get(url).query(&[("startDate", from), ("endDate", to)]);
    if !st.nasa_key.is_empty() {
        req = req.query(&[("api_key", &st.nasa_key)]);
    }
    let json: Value = req.send().await?.json().await?;
    write_cache(&st.pool, "cme", json).await
}
