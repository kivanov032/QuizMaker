import {Outlet} from "react-router-dom";

export default function CreatorLayout() {

    return (
        <div id="creatorLayout">
            <header>
                <h1>Я в CreatorLayout</h1>
            </header>
            <main>
                <Outlet/>
            </main>
        </div>
    );
}
