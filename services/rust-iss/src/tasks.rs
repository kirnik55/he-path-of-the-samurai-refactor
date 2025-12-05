// src/tasks.rs
use std::time::Duration;

use tokio::time::sleep;
use tracing::error;

use crate::services::{
    apod::fetch_apod,
    donki::fetch_donki,
    iss::fetch_and_store_iss,
    neo::fetch_neo_feed,
    osdr::fetch_and_store_osdr,
    spacex::fetch_spacex_next,
};
use crate::state::AppState;

pub fn spawn_background_jobs(state: &AppState) {
    // OSDR
    {
        let st = state.clone();
        tokio::spawn(async move {
            loop {
                if let Err(e) = fetch_and_store_osdr(&st).await {
                    error!("osdr err {e:?}");
                }
                sleep(Duration::from_secs(st.every_osdr)).await;
            }
        });
    }

    // ISS
    {
        let st = state.clone();
        tokio::spawn(async move {
            loop {
                if let Err(e) = fetch_and_store_iss(&st.pool, &st.fallback_url).await {
                    error!("iss err {e:?}");
                }
                sleep(Duration::from_secs(st.every_iss)).await;
            }
        });
    }

    // APOD
    {
        let st = state.clone();
        tokio::spawn(async move {
            loop {
                if let Err(e) = fetch_apod(&st).await {
                    error!("apod err {e:?}");
                }
                sleep(Duration::from_secs(st.every_apod)).await;
            }
        });
    }

    // NeoWs
    {
        let st = state.clone();
        tokio::spawn(async move {
            loop {
                if let Err(e) = fetch_neo_feed(&st).await {
                    error!("neo err {e:?}");
                }
                sleep(Duration::from_secs(st.every_neo)).await;
            }
        });
    }

    // DONKI
    {
        let st = state.clone();
        tokio::spawn(async move {
            loop {
                if let Err(e) = fetch_donki(&st).await {
                    error!("donki err {e:?}");
                }
                sleep(Duration::from_secs(st.every_donki)).await;
            }
        });
    }

    // SpaceX
    {
        let st = state.clone();
        tokio::spawn(async move {
            loop {
                if let Err(e) = fetch_spacex_next(&st).await {
                    error!("spacex err {e:?}");
                }
                sleep(Duration::from_secs(st.every_spacex)).await;
            }
        });
    }
}
