import {ContentBlockComponent} from "./content-block/content-block.component";

declare var angular: angular.IAngularStatic;

import {piwikSiteselectorAdapter} from "./siteselector/siteselector.adapter";
import {NgModule, StaticProvider} from '@angular/core';
import {downgradeComponent, downgradeModule, UpgradeModule} from '@angular/upgrade/static';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import {BrowserModule} from "@angular/platform-browser";
import {SiteSelectorAllSitesLink, SiteSelectorComponent} from './siteselector/siteselector.component';
import {SitesService} from "./site-store/sites.service";
import {MatomoApiService} from "./matomo-api/matomo-api.service";
import {HttpClientModule} from "@angular/common/http";
import {FocusAnywhereButHereDirective} from "./common/focus-anywhere-but-here.directive";
import {TranslatePipe} from "./common/translate.pipe";
import {FormsModule} from "@angular/forms";
import {EnrichedHeadlineComponent} from "./enriched-headline/enriched-headline.component";
import {RateFeatureComponent, ReviewLinksComponent} from "./rate-feature/rate-feature.component";
import {MatomoDialogDirective} from "./common/matomo-dialog.directive";
import {piwikEnrichedHeadlineAdapter} from "./enriched-headline/enriched-headline.adapter";
import {CommonModule} from "@angular/common";
import {piwikContentBlockAdapter} from "./content-block/content-block.adapter";

export * from './matomo-api/matomo-api.service';
export * from './common/focus-anywhere-but-here.directive';
export * from './common/translate.pipe';
export * from './site-store/sites.service';
export * from './site-store/site';
export * from './siteselector/siteselector.component';
export * from './rate-feature/rate-feature.component';
export * from './enriched-headline/enriched-headline.component';
export * from './content-block/content-block.component';

@NgModule({
  declarations: [
    SiteSelectorComponent,
    FocusAnywhereButHereDirective,
    TranslatePipe,
    SiteSelectorAllSitesLink,
    ContentBlockComponent,
    EnrichedHeadlineComponent,
    RateFeatureComponent,
    ReviewLinksComponent,
    MatomoDialogDirective,
  ],
  imports: [
    CommonModule,
    BrowserModule,
    UpgradeModule,
    HttpClientModule,
    FormsModule,
  ],
  exports: [
    SiteSelectorComponent,
    FocusAnywhereButHereDirective,
    TranslatePipe,
    ContentBlockComponent,
    EnrichedHeadlineComponent,
    RateFeatureComponent,
  ],
  providers: [
    FocusAnywhereButHereDirective,
    TranslatePipe,
    MatomoApiService,
    SitesService,
  ],
  entryComponents: [
    SiteSelectorComponent,
    ContentBlockComponent,
    EnrichedHeadlineComponent,
  ],
})
export class CoreHomeModule {
  ngDoBootstrap() {
    // empty
  }
}

const ng2BootstrapFn = (extraProviders: StaticProvider[]) =>
    platformBrowserDynamic(extraProviders).bootstrapModule(CoreHomeModule);

export const angularModuleName = downgradeModule(ng2BootstrapFn);

angular.module(angularModuleName).directive('piwikSiteselectorDowngrade', downgradeComponent(
    { component: SiteSelectorComponent, downgradedModule: angularModuleName }));
angular.module(angularModuleName).directive('piwikSiteselector', piwikSiteselectorAdapter);

angular.module(angularModuleName).directive('piwikEnrichedHeadlineDowngrade', downgradeComponent(
    { component: EnrichedHeadlineComponent, downgradedModule: angularModuleName }));
angular.module(angularModuleName).directive('piwikEnrichedHeadline', piwikEnrichedHeadlineAdapter);

angular.module(angularModuleName).directive('piwikContentBlockDowngrade', downgradeComponent(
    { component: ContentBlockComponent, downgradedModule: angularModuleName }));
angular.module(angularModuleName).directive('piwikContentBlock', piwikContentBlockAdapter);